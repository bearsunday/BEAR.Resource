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
        $parameters = $this->handleParams($query, $names);

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
        $annotations = $this->reader->getMethodAnnotations($method);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof ResourceParam) {
                $names[$annotation->param] = $annotation;
            }
            if ($annotation instanceof Assisted) {
                /* @var $annotation Assisted */
                foreach ($annotation->values as $assistedParam) {
                    $names[$assistedParam] = $annotation;
                }
            }
        }

        return $names;
    }

    /**
     * @param string[] $query caller value
     * @param string[] $names default value ['param-name' => 'param-type|param-value']
     *
     * @return array
     */
    private function handleParams(array $query, array $names)
    {
        $parameters = [];
        foreach ($names as $name => $param) {
            // @ResourceParam value
            if ($param instanceof ResourceParam) {
                $parameters[] = $this->getResourceParam($param, $query);
                continue;
            }
            // @Assisted (method injection) value
            if ($param instanceof Assisted) {
                $parameters[] = null;
                continue;
            }
            // query value
            if (isset($query[$name])) {
                $parameters[] = $query[$name];
                continue;
            }
            // default value
            if (is_scalar($param) || $param === null) {
                $parameters[] = $param;
                continue;
            }
            throw new ParameterException($name);
        }

        return $parameters;
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
}
