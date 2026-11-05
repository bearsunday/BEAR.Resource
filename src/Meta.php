<?php

declare(strict_types=1);

namespace BEAR\Resource;

use LogicException;
use ReflectionClass;
use ReflectionMethod;

use function array_shift;
use function assert;
use function class_exists;
use function explode;
use function implode;
use function is_string;
use function str_starts_with;
use function strtolower;
use function substr;

/**
 * @psalm-import-type PackageMetadata from Types
 * @psalm-import-type ResourceClassName from Types
 */
final class Meta
{
    private const EXTRAS_VENDOR = 'vendor';
    private const EXTRAS_PACKAGE = 'package';

    /** @var string */
    public $uri;

    /** @var Options */
    public $options;

    /** @var PackageMetadata */
    public $extras = [];

    /**
     * @param ResourceClassName $class
     *
     * @psalm-capabilities write-this-props|write-props|read-globals|write-globals|io|time
     */
    public function __construct(string $class)
    {
        $this->uri = $this->getUri($class);
        $this->options = $this->getOptions($class);
    }

    /** @psalm-capabilities read-props|write-this-props */
    private function getUri(string $class): string
    {
        /** @var list<string> $classPath */
        $classPath = explode('\\', $class);
        // $class
        $vendor = array_shift($classPath);
        assert(is_string($vendor));
        $this->extras[self::EXTRAS_VENDOR] = $vendor;
        $package = array_shift($classPath);
        assert(is_string($package));
        $this->extras[self::EXTRAS_PACKAGE] = $package;
        array_shift($classPath); // "/Resource/"
        $scheme = array_shift($classPath);
        assert(is_string($scheme));

        return strtolower("{$scheme}://self/" . implode('/', $classPath));
    }

    /**
     * Return available resource request method
     *
     * @param ResourceClassName $class
     *
     * @psalm-impure
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
     *
     * @psalm-capabilities read-props
     */
    private function getAllows(array $methods): array
    {
        $allows = [];
        foreach ($methods as $method) {
            $isRequestMethod = str_starts_with($method->name, 'on') && ! str_starts_with($method->name, 'onLink');
            if (! $isRequestMethod) {
                continue;
            }

            $allows[] = strtolower(substr($method->name, 2));
        }

        return $allows;
    }

    /**
     * @param ResourceClassName $class
     *
     * @psalm-capabilities write-props|read-globals|write-globals|io|time
     */
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
