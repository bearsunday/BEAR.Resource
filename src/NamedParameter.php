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

    public function __construct(Cache $cache, Reader $reader, InjectorInterface $injector)
    {
        $this->cache = $cache;
        $this->reader = $reader;
        $this->injector = $injector;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(array $callable, array $query)
    {
        $id = __CLASS__ . get_class($callable[0]) . $callable[1];
        $names = $this->cache->fetch($id);
        if (! $names) {
            $names = $this->getNamedParamMetas($callable);
            $this->cache->save($id, $names);
        }
        $parameters = $this->evaluateParams($query, $names);

        return $parameters;
    }

    /**
     * @param string[] $query caller value
     * @param string[] $names default value ['param-name' => 'param-type|param-value']
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
     * @return array
     */
    private function overrideAssistedParam(\ReflectionMethod $method, array $names)
    {
        $annotations = $this->reader->getMethodAnnotations($method);
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

    /**
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
