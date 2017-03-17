<?php
/**
 * This file is part of the BEAR.Sunday package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

final class OptionProvider implements OptionProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function get(ResourceObject $ro)
    {
        $options = $this->getOptions($ro);
        $ro->headers['allow'] = (string) $options;
        $ro->body = null;

        return $ro;
    }

    /**
     * Return available resource request method
     *
     * @param ResourceObject $resourceObject
     *
     * @return Options
     */
    private function getOptions(ResourceObject $resourceObject)
    {
        $allows = $this->getAllows((new \ReflectionClass($resourceObject))->getMethods());
        $params = [];
        foreach ($allows as $method) {
            $params[] = $this->getParams($resourceObject, $method);
        }

        return new Options($allows, $params);
    }

    /**
     * @param \ReflectionMethod[] $methods
     *
     * @return array
     */
    private function getAllows(array $methods)
    {
        $allows = [];
        foreach ($methods as $method) {
            $isRequestMethod = strpos($method->name, 'on') === 0 && strpos($method->name, 'onLink') !== 0;
            if ($isRequestMethod) {
                $allows[] = strtolower(substr($method->name, 2));
            }
        }

        return $allows;
    }

    /**
     * @param ResourceObject $ro
     * @param string         $method
     *
     * @return string[]
     */
    private function getParams($ro, $method)
    {
        $params = [];
        $refMethod = new \ReflectionMethod($ro, 'on' . $method);
        $parameters = $refMethod->getParameters();
        $paramArray = [];
        foreach ($parameters as $parameter) {
            $name = $parameter->name;
            $param = $parameter->isOptional() ? "({$name})" : $name;
            $paramArray[] = $param;
        }
        $key = "param-{$method}";
        $params[$key] = implode(',', $paramArray);

        return $params;
    }
}
