<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Annotation\ResourceParam;
use BEAR\Resource\Exception\ParameterException;
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
            $default = $parameter->isDefaultValueAvailable() === true ? $parameter->getDefaultValue() : new Param(get_class($callable[0]), $callable[1], $parameter->name);
            $names[$parameter->name] = $default;
        }
        $names = $this->SetAnnotationMetas($method, $names);

        return $names;
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
        foreach ($names as $name => $param) {
            $parameters[] = $this->getParamValue($param, $query, $name);
        }

        return $parameters;
    }

    private function getParamValue($param, array $query, $name)
    {
        // @ResourceParam value
        if ($param instanceof ResourceParam) {
            return $this->getResourceParam($param, $query);
        }
        // @Assisted (method injection) value
        if ($param instanceof Assisted) {
            return null;
        }
        // query value
        if (isset($query[$name])) {
            return $query[$name];
        }
        // default value
        if (is_scalar($param) || $param === null) {
            return $param;
        }
        throw new ParameterException($name);
    }

    /**
     * @param ResourceParam $resourceParam
     * @param array         $query
     *
     * @return mixed
     */
    private function getResourceParam(ResourceParam $resourceParam, array $query)
    {
        $uri = $resourceParam->templated === true ? uri_template($resourceParam->uri, $query) : $resourceParam->uri;
        $resource = $this->injector->getInstance(ResourceInterface::class);
        $resourceResult = $resource->get->uri($uri)->eager->request();
        $fragment = parse_url($uri, PHP_URL_FRAGMENT);

        return $resourceResult[$fragment];
    }

    /**
     * @return array
     */
    private function SetAnnotationMetas(\ReflectionMethod $method, array $names)
    {
        $annotations = $this->reader->getMethodAnnotations($method);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof ResourceParam) {
                $names[$annotation->param] = $annotation;
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
            $names[$assistedParam] = $assisted;
        }
        return $names;
    }
}
