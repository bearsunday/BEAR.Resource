<?php

declare(strict_types=1);

namespace BEAR\Resource;

use LogicException;
use ReflectionClass;
use ReflectionMethod;

use function array_shift;
use function class_exists;
use function explode;
use function implode;
use function strpos;
use function strtolower;
use function substr;

final class Meta
{
    private const EXTRAS_VENDOR = 'vendor';
    private const EXTRAS_PACKAGE = 'package';

    /** @var string */
    public $uri;

    /** @var Options */
    public $options;

    /** @var array{vendor?: string, package?: string} */
    public $extras = [];

    /** @param class-string $class */
    public function __construct(string $class)
    {
        $this->uri = $this->getUri($class);
        $this->options = $this->getOptions($class);
    }

    private function getUri(string $class): string
    {
        $classPath = explode('\\', $class);
        // $class
        $this->extras[self::EXTRAS_VENDOR] = array_shift($classPath); // @phpstan-ignore-line
        $this->extras[self::EXTRAS_PACKAGE] = array_shift($classPath); // @phpstan-ignore-line
        array_shift($classPath); // "/Resource/"
        $scheme = array_shift($classPath);

        return strtolower("{$scheme}://self/" . implode('/', $classPath));
    }

    /**
     * Return available resource request method
     */
    private function getOptions(string $class): Options
    {
        if (! class_exists($class)) {
            throw new LogicException(); // @codeCoverageIgnore
        }

        $ref = new ReflectionClass($class);
        $allows = $this->getAllows($ref->getMethods());
        $params = [];
        foreach ($allows as $method) {
            $params[] = $this->getParams($class, $method);
        }

        return new Options($allows, $params);
    }

    /**
     * @param ReflectionMethod[] $methods
     *
     * @return string[]
     * @psalm-return list<string>
     */
    private function getAllows(array $methods): array
    {
        $allows = [];
        foreach ($methods as $method) {
            $isRequestMethod = strpos($method->name, 'on') === 0 && strpos($method->name, 'onLink') !== 0;
            if (! $isRequestMethod) {
                continue;
            }

            $allows[] = strtolower(substr($method->name, 2));
        }

        return $allows;
    }

    /** @param class-string $class */
    private function getParams(string $class, string $method): Params
    {
        $refMethod = new ReflectionMethod($class, 'on' . $method);
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
