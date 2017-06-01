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
     *
     * @return array
     */
    private function evaluateParams(array $query, array $names)
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
     *
     * @param array $callable
     *
     * @return array
     */
    private function getNamedParamMetas(array $callable)
    {
        $method = new \ReflectionMethod($callable[0], $callable[1]);
        $parameters = $method->getParameters();
        $names = [];
        foreach ($parameters as $parameter) {
            $names[$parameter->name] = $parameter->isDefaultValueAvailable() === true ? new OptionalParam($parameter->getDefaultValue()) : new RequiredParam;
        }
        $names = $this->overrideAssistedParam($method, $names);

        return $names;
    }

    /**
     * Set "method injection" parameter
     *
     * @return array
     */
    private function overrideAssistedParam(\ReflectionMethod $method, array $names)
    {
        $globals = $this->globals ? $this->globals : $GLOBALS;
        $annotations = $this->reader->getMethodAnnotations($method);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof ResourceParam) {
                $names[$annotation->param] = new AssistedResourceParam($annotation);
            }
            if ($annotation instanceof Assisted) {
                $names = $this->setAssistedAnnotation($names, $annotation);
            }
            if ($annotation instanceof AbstractWebContextParam) {
                $names[$annotation->param] = new AssistedWebContextParam($annotation, $globals);
            }
        }

        return $names;
    }

    /**
     * Set AssistedParam objects
     *
     * null is used for Assisted interceptor
     *
     * @return array
     */
    private function setAssistedAnnotation(array $names, Assisted $assisted)
    {
        /* @var $annotation Assisted */
        foreach ($assisted->values as $assistedParam) {
            $names[$assistedParam] = new AssistedParam;
        }

        return $names;
    }
}
