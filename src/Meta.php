<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

final class Meta
{
    /**
     * @var string
     */
    public $uri;

    /**
     * @var array
     */
    public $options = [];

    /**
     * @var array
     */
    public $extras = [];

    CONST EXTRAS_VENDOR = 'vendor';
    CONST EXTRAS_PACKAGE = 'package';

    /**
     * @param string $class
     */
    public function __construct($class)
    {
        $this->class = $class;
        $this->uri = $this->getUri($class);
        $this->options = $this->getOptions($class);
    }

    /**
     * @param $class
     *
     * @return string
     */
    private function getUri($class)
    {
        $classPath = explode('\\', $class);
        // $class
        $this->extras[self::EXTRAS_VENDOR] = array_shift($classPath);
        $this->extras[self::EXTRAS_PACKAGE] = array_shift($classPath);
        array_shift($classPath); // "/Resource/"
        $scheme = array_shift($classPath);
        $uri = strtolower("{$scheme}://self/" . implode('/', $classPath));

        return $uri;
    }

    /**
     * Return available resource request method
     *
     * @param ResourceObject $ro
     *
     * @return array
     */
    private function getOptions($ro)
    {
        $ref = new \ReflectionClass($ro);
        $methods = $ref->getMethods();
        $allow = [];
        foreach ($methods as $method) {
            $isRequestMethod = (substr($method->name, 0, 2) === 'on') && (substr($method->name, 0, 6) !== 'onLink');
            if ($isRequestMethod) {
                $allow[] = strtolower(substr($method->name, 2));
            }
        }
        $params = [];
        foreach ($allow as $method) {
            $refMethod = new \ReflectionMethod($ro, 'on' . $method);
            $parameters = $refMethod->getParameters();
            $params = [];
            $optionalParams = $requiredParams = [];
            foreach ($parameters as $parameter) {
                $name = $parameter->name;
                if ($parameter->isOptional()) {
                    $optionalParams[] = $name;
                    continue;
                }
                $requiredParams[] = $name;
            }
            $params[] = new Params($method, $requiredParams, $optionalParams);
        }
        $options = new Options($allow, $params);

        return $options;
    }
}
