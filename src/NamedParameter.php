<?php
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
    public function getParameters(array $callable, array $query)
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
     * Return named parameter information
     */
    private function getNamedParamMetas(callable $callable) : array
    {
        $method = new \ReflectionMethod($callable[0], $callable[1]);
        $parameters = $method->getParameters();
        list($assistedNames, $webcontext) = $this->setAssistedParam($method);
        $names = [];
        foreach ($parameters as $parameter) {
            if (isset($assistedNames[$parameter->name])) {
                $names[$parameter->name] = $assistedNames[$parameter->name];
                continue;
            }
            if (isset($webcontext[$parameter->name])) {
                $default = $parameter->isDefaultValueAvailable() === true ? new DefaultParam($parameter->getDefaultValue()) : new NoDefaultParam();
                $names[$parameter->name] = new AssistedWebContextParam($webcontext[$parameter->name], $default);
                continue;
            }
            $names[$parameter->name] = $parameter->isDefaultValueAvailable() === true ? new OptionalParam($parameter->getDefaultValue()) : new RequiredParam;
        }

        return $names;
    }

    /**
     * Set "method injection" parameter
     */
    private function setAssistedParam(\ReflectionMethod $method) : array
    {
        $names = $webcontext = [];
        $annotations = $this->reader->getMethodAnnotations($method);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof ResourceParam) {
                $names[$annotation->param] = new AssistedResourceParam($annotation);
            }
            if ($annotation instanceof Assisted) {
                $names = $this->setAssistedAnnotation($names, $annotation);
            }
            if ($annotation instanceof AbstractWebContextParam) {
                $webcontext[$annotation->param] = $annotation;
            }
        }

        return [$names, $webcontext];
    }

    /**
     * Set AssistedParam objects
     s     */
    private function setAssistedAnnotation(array $names, Assisted $assisted) : array
    {
        /* @var $annotation Assisted */
        foreach ($assisted->values as $assistedParam) {
            $names[$assistedParam] = new AssistedParam;
        }

        return $names;
    }
}
