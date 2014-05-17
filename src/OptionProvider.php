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
     * @param ResourceObject $ro
     *
     * @return array
     */
    private function getOptions(ResourceObject $ro)
    {
        $ref = new \ReflectionClass($ro);
        $methods = $ref->getMethods();
        $allow = $params = [];
        foreach ($methods as $method) {
            $isRequestMethod = (substr($method->name, 0, 2) === 'on') && (substr($method->name, 0, 6) !== 'onLink');
            if ($isRequestMethod) {
                $allow[] = strtolower(substr($method->name, 2));
            }
        }
        foreach ($allow as $method) {
            $params = $this->getParams($ro, $method);
        }
        $result = ['allow' => $allow, 'params' => $params];

        return $result;
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
