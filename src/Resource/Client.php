<?php
namespace BEAR\Resource;

use Ray\Di\Annotation;

use BEAR\Resource\Object as ResourceObject, Ray\Di\Config;


class Client implements Resource
{
    public function __construct(Config $config)
    {
        $this->config = $config;
    }
    
    public function post(ResourceObject $ro, array $args)
    {    
        $params = $this->getParams($ro, 'onPost', $args);
        return call_user_func_array(array($ro, 'onPost'), $params);
    }
    
    public function put(ResourceObject $ro, array $args)
    {    
        $params = $this->getParams($ro, 'onPut', $args);
        return call_user_func_array(array($ro, 'onPut'), $params);
    }
    
    /**
     * Get named parameters
     *
     * @param object $object
     * @param string $method
     * @param array $args
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    private function getParams($object, $method, array $args)
    {
        $parameters = $this->config->getMethodReflect($object, $method)->getParameters();
        foreach ($parameters as $parameter) {
            if (isset($args[$parameter->name])) {
                $params[] = $args[$parameter->name];
            } elseif ($parameter->isDefaultValueAvailable() === true) {
                $params[] = $parameter->getDefaultValue();
            } else {
                throw new Exception\InvalidParameter($parameter->name);
            }
        }
        return $params;
    }
    
}