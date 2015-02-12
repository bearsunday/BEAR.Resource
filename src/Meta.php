<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

final class Meta
{
    const EXTRAS_VENDOR = 'vendor';

    const EXTRAS_PACKAGE = 'package';

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

    /**
     * @param string $class
     */
    public function __construct($class)
    {
        $this->uri = $this->getUri($class);
        $this->options = $this->getOptions($class);
    }

    /**
     * @param string $class
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
     * @param string $class
     *
     * @return Options
     */
    private function getOptions($class)
    {
        $ref = new \ReflectionClass($class);
        $allows = $this->getAllows($ref->getMethods());
        $params = [];
        foreach ($allows as $method) {
            $params[] = $this->getParams($class, $method);
        }
        $options = new Options($allows, $params);

        return $options;
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
            $isRequestMethod = (substr($method->name, 0, 2) === 'on') && (substr($method->name, 0, 6) !== 'onLink');
            if ($isRequestMethod) {
                $allows[] = strtolower(substr($method->name, 2));
            }
        }

        return $allows;
    }

    /**
     * @param string $class
     * @param string $method
     *
     * @return Params
     */
    private function getParams($class, $method)
    {
        $refMethod = new \ReflectionMethod($class, 'on' . $method);
        $parameters = $refMethod->getParameters();
        $optionalParams = $requiredParams = [];
        foreach ($parameters as $parameter) {
            $name = $parameter->name;
            if ($parameter->isOptional()) {
                $optionalParams[] = $name;
                continue;
            }
            $requiredParams[] = $name;
        }

        return new Params($method, $requiredParams, $optionalParams);
    }
}
