<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Annotation\ResourceParam;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\Cache;
use Ray\Di\Di\Assisted;
use Ray\Di\InjectorInterface;
use Ray\WebContextParam\Annotation\AbstractWebContextParam;

final class NamedParameter implements NamedParameterInterface
{
    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var InjectorInterface
     */
    private $injector;

    /**
     * @var array
     */
    private $globals;

    public function __construct(Cache $cache, Reader $reader, InjectorInterface $injector, array $globals = [])
    {
        $this->cache = $cache;
        $this->reader = $reader;
        $this->injector = $injector;
        $this->globals = $globals;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(callable $callable, array $query) : array
    {
        $cacheId = __CLASS__ . get_class($callable[0]) . $callable[1];
        $names = $this->cache->fetch($cacheId);
        if (! $names) {
            $names = $this->getNamedParamMetas($callable);
            $this->cache->save($cacheId, $names);
        }
        $parameters = $this->evaluateParams($query, $names);

        return $parameters;
    }

    /**
     * Return evaluated parameters
     *
     * @param array            $query caller value
     * @param ParamInterface[] $names Param object[] ['varName' => ParamInterface]
     */
    private function evaluateParams(array $query, array $names) : array
    {
        $parameters = [];
        foreach ($names as $varName => $param) {
            /* @var $param ParamInterface */
            $parameters[] = $param($varName, $query, $this->injector);
        }

        return $parameters;
    }

    /**
     * Return named parameter metas
     */
    private function getNamedParamMetas(callable $callable) : array
    {
        $method = new \ReflectionMethod($callable[0], $callable[1]);
        $parameters = $method->getParameters();
        $annotations = $this->reader->getMethodAnnotations($method);
        $assistedNames = $this->getAssistedNames($annotations);
        $webContext = $this->getWebContext($annotations);
        $namedParamMetas = $this->addNamedParams($parameters, $assistedNames, $webContext);

        return $namedParamMetas;
    }

    private function getAssistedNames(array $annotations) : array
    {
        $names = [];
        foreach ($annotations as $annotation) {
            if ($annotation instanceof ResourceParam) {
                $names[$annotation->param] = new AssistedResourceParam($annotation);
            }
            if ($annotation instanceof Assisted) {
                $names = $this->setAssistedAnnotation($names, $annotation);
            }
        }

        return $names;
    }

    private function getWebContext(array $annotations) : array
    {
        $webcontext = [];
        foreach ($annotations as $annotation) {
            if ($annotation instanceof AbstractWebContextParam) {
                $webcontext[$annotation->param] = $annotation;
            }
        }

        return $webcontext;
    }

    private function setAssistedAnnotation(array $names, Assisted $assisted) : array
    {
        /* @var $annotation Assisted */
        foreach ($assisted->values as $assistedParam) {
            $names[$assistedParam] = new AssistedParam;
        }

        return $names;
    }

    /**
     * @param \ReflectionParameter[] $parameters
     * @param array                  $assistedNames
     * @param array                  $webcontext
     *
     * @return ParamInterface[]
     */
    private function addNamedParams(array $parameters, array $assistedNames, array $webcontext) : array
    {
        $names = [];
        foreach ($parameters as $parameter) {
            if (isset($assistedNames[$parameter->name])) {
                $names[$parameter->name] = $assistedNames[$parameter->name];
                continue;
            }
            if (isset($webcontext[$parameter->name])) {
                $default = $this->getDefault($parameter);
                $names[$parameter->name] = new AssistedWebContextParam($webcontext[$parameter->name], $default);
                continue;
            }
            $names[$parameter->name] = $this->getParam($parameter);
        }

        return $names;
    }

    private function getDefault(\ReflectionParameter $parameter) : ParamInterface
    {
        return $parameter->isDefaultValueAvailable() === true ? new DefaultParam($parameter->getDefaultValue()) : new NoDefaultParam();
    }

    private function getParam(\ReflectionParameter $parameter) : ParamInterface
    {
        return $parameter->isDefaultValueAvailable() === true ? new OptionalParam($parameter->getDefaultValue()) : new RequiredParam;
    }
}
