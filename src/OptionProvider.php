<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
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
        $ro->headers['allow'] = $options['allow'];
        $ro->headers += $options['params'];
        $ro->body = null;

        return $ro;
    }

    /**
     * Return available resource request method
     *
     * @param ResourceObject $resourceObject
     *
     * @return array
     */
    private function getOptions(ResourceObject $resourceObject)
    {
        $allows = $this->getAllows((new \ReflectionClass($resourceObject))->getMethods());
        $params = [];
        foreach ($allows as $method) {
            $params[] = $this->getParams($resourceObject, $method);
        }
        $result = ['allow' => $allows, 'params' => $params];

        return $result;
    }

    /**
     * @param array $methods
     *
     * @return array
     */
    private function getAllows(array $methods)
    {
        $allows = [];
        foreach ($methods as $method) {
            $isRequestMethod = (substr($method->name, 0, 2) === 'on') && (substr($method->name, 0, 6) !== 'onLink');
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
