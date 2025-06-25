<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BackedEnum;
use BEAR\Resource\Annotation\Input;
use BEAR\Resource\Exception\ParameterEnumTypeException;
use BEAR\Resource\Exception\ParameterException;
use BEAR\Resource\Exception\ParameterInvalidEnumException;
use InvalidArgumentException;
use Ray\Di\InjectorInterface;
use ReflectionClass;
use ReflectionEnum;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use Throwable;
use UnitEnum;

use function assert;
use function class_exists;
use function count;
use function enum_exists;
use function is_a;
use function is_array;
use function is_int;
use function is_iterable;
use function is_string;
use function ltrim;
use function preg_replace;
use function strtolower;

/**
 * @psalm-type DependencyMeta = array{0:class-string|'', 1:string}
 * @psalm-type DependencyMetas = array<string, DependencyMeta>
 */
final class InputParam implements ParamInterface
{
    private readonly string $type;
    private readonly bool $isDefaultAvailable;
    private readonly mixed $defaultValue;

    /** @var DependencyMetas */
    private array $dependenciesMetas = [];

    public function __construct(
        ReflectionNamedType $type,
        private readonly ReflectionParameter $parameter,
    ) {
        $this->type = $type->getName();
        $this->isDefaultAvailable = $parameter->isDefaultValueAvailable();

        if ($this->isDefaultAvailable) {
            $this->defaultValue = $parameter->getDefaultValue();
        }

        // Initialize dependency metadata for constructor injection
        $this->dependenciesMetas = (new ConstructoeDependencies())(new ReflectionClass($this->type));
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(string $varName, array $query, InjectorInterface $injector)
    {
        assert(class_exists($this->type) || enum_exists($this->type));
        $refClass = new ReflectionClass($this->type);

        // Handle enums
        if ($refClass->isEnum()) {
            return $this->createEnum($varName, $query);
        }

        $constructor = $refClass->getConstructor();
        if ($constructor === null) {
            // Handle classes without constructor (like ClassParam does)
            return $this->createWithoutConstructor($refClass, $varName, $query);
        }

        // Check if this parameter has #[Input] attribute
        $inputAttr = $this->getInputAttribute($this->parameter);

        // If no #[Input] attribute, use ClassParam behavior (structured data with parameter name as key)
        if ($inputAttr === null) {
            return $this->createFromStructuredData($refClass, $constructor, $varName, $query, $injector);
        }

        // If #[Input] has key specified, use structured data approach
        if ($inputAttr->key !== null) {
            return $this->createFromStructuredData($refClass, $constructor, $inputAttr->key, $query, $injector);
        }

        try {
            $constructorArgs = [];

            foreach ($constructor->getParameters() as $param) {
                $paramName = $param->getName();

                // Check if parameter has #[Input] attribute for nested input objects
                $inputAttr = $this->getInputAttribute($param);
                if ($inputAttr !== null) {
                    $paramType = $param->getType();
                    if ($paramType instanceof ReflectionNamedType) {
                        $nestedInputParam = new InputParam($paramType, $param);
                        $constructorArgs[] = $nestedInputParam($paramName, $query, $injector);
                        continue;
                    }
                }

                // Check if parameter needs DI (skip built-in types)
                if (isset($this->dependenciesMetas[$paramName])) {
                    [$className, $qualifier] = $this->dependenciesMetas[$paramName];
                    if (! empty($className) && class_exists($className)) {
                        $constructorArgs[] = $injector->getInstance($className, $qualifier);
                        continue;
                    }
                }

                // Use query parameter if available (with snake_case/kebab-case support)
                $paramValue = $this->getParamValue($paramName, $query);
                if ($paramValue !== null) {
                    $constructorArgs[] = $paramValue;
                    continue;
                }

                // Use default value if available
                if ($param->isDefaultValueAvailable()) {
                    $constructorArgs[] = $param->getDefaultValue();
                    continue;
                }

                throw new ParameterException("Required parameter '{$paramName}' not found for {$this->type}");
            }

            return $refClass->newInstanceArgs($constructorArgs);
        } catch (Throwable $e) {
            // Re-throw validation exceptions directly
            if ($e instanceof InvalidArgumentException) {
                throw $e;
            }

            throw new ParameterException("Failed to create {$this->type}: " . $e->getMessage(), 0, $e);
        }
    }

    private function getInputAttribute(ReflectionParameter $param): Input|null
    {
        $attributes = $param->getAttributes(Input::class);
        if (count($attributes) === 0) {
            return null;
        }

        return $attributes[0]->newInstance();
    }

    /**
     * Get parameter value from query with camelCase/kebab-case support
     *
     * @param array<string, mixed> $query
     */
    private function getParamValue(string $paramName, array $query): mixed
    {
        // Try exact match first
        if (isset($query[$paramName])) {
            return $query[$paramName];
        }

        // Try kebab-case version (camelCase -> kebab-case)
        $kebabName = ltrim(strtolower((string) preg_replace('/[A-Z]/', '-\0', $paramName)), '-');
        if (isset($query[$kebabName])) {
            return $query[$kebabName];
        }

        return null;
    }

    /**
     * Create object from structured data (ClassParam style)
     */
    private function createFromStructuredData(
        ReflectionClass $refClass,
        ReflectionMethod $constructor,
        string $key,
        array $query,
        InjectorInterface $injector,
    ): mixed {
        // Get structured data from the specified key
        if (! isset($query[$key])) {
            if ($this->isDefaultAvailable) {
                return $this->defaultValue;
            }

            throw new ParameterException("Required key '{$key}' not found for {$this->type}");
        }

        $data = $query[$key];
        if (! is_array($data)) {
            throw new ParameterException("Data under key '{$key}' must be an array for {$this->type}");
        }

        try {
            $constructorArgs = [];

            foreach ($constructor->getParameters() as $param) {
                $paramName = $param->getName();

                // Check if parameter has #[Input] attribute for nested input objects
                $inputAttr = $this->getInputAttribute($param);
                if ($inputAttr !== null) {
                    $paramType = $param->getType();
                    if ($paramType instanceof ReflectionNamedType) {
                        $nestedInputParam = new InputParam($paramType, $param);
                        $constructorArgs[] = $nestedInputParam($paramName, $data, $injector);
                        continue;
                    }
                }

                // Check if parameter needs DI (skip built-in types)
                if (isset($this->dependenciesMetas[$paramName])) {
                    [$className, $qualifier] = $this->dependenciesMetas[$paramName];
                    if (! empty($className) && class_exists($className)) {
                        $constructorArgs[] = $injector->getInstance($className, $qualifier);
                        continue;
                    }
                }

                // Use data parameter if available
                if (isset($data[$paramName])) {
                    $constructorArgs[] = $data[$paramName];
                    continue;
                }

                // Use default value if available
                if ($param->isDefaultValueAvailable()) {
                    $constructorArgs[] = $param->getDefaultValue();
                    continue;
                }

                throw new ParameterException("Required parameter '{$paramName}' not found in key '{$key}' for {$this->type}");
            }

            return $refClass->newInstanceArgs($constructorArgs);
        } catch (Throwable $e) {
            // Re-throw validation exceptions directly
            if ($e instanceof InvalidArgumentException) {
                throw $e;
            }

            throw new ParameterException("Failed to create {$this->type} from key '{$key}': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Create enum from query parameter (ClassParam style)
     */
    private function createEnum(string $varName, array $query): mixed
    {
        try {
            // Get the value using ClassParam behavior (snake_case conversion)
            $props = $this->getPropsForClassParam($varName, $query);
        } catch (ParameterException $e) {
            // If no value found and default is available, return it
            if ($this->isDefaultAvailable) {
                return $this->defaultValue;
            }

            throw $e;
        }

        /** @var class-string<UnitEnum> $type */
        $type = $this->type;
        $refEnum = new ReflectionEnum($type);
        assert(enum_exists($type));

        if (! $refEnum->isBacked()) {
            throw new NotBackedEnumException($type);
        }

        assert(is_a($type, BackedEnum::class, true));
        if (! (is_int($props) || is_string($props))) {
            // If props is not a scalar but we have a default value, return it
            if ($this->isDefaultAvailable) {
                return $this->defaultValue;
            }

            throw new ParameterEnumTypeException($varName);
        }

        // Get the backing type of the enum
        $backingType = $refEnum->getBackingType();
        if ($backingType && $backingType->getName() === 'int' && is_string($props)) {
            // Convert string to int for int-backed enums
            $props = (int) $props;
        }

        /** @psalm-suppress MixedAssignment */
        $value = $type::tryFrom($props);
        if ($value === null) {
            throw new ParameterInvalidEnumException($varName);
        }

        return $value;
    }

    /**
     * Create object without constructor (ClassParam style)
     */
    private function createWithoutConstructor(ReflectionClass $refClass, string $varName, array $query): mixed
    {
        // Get the props using ClassParam behavior
        $props = $this->getPropsForClassParam($varName, $query);

        if (! is_iterable($props)) {
            if ($this->isDefaultAvailable) {
                return $this->defaultValue;
            }

            throw new ParameterException("Expected array data for {$this->type}");
        }

        /** @psalm-suppress MixedMethodCall */
        $obj = new $this->type();
        /** @psalm-suppress MixedAssignment */
        foreach ($props as $propName => $propValue) {
            $obj->{$propName} = $propValue;
        }

        return $obj;
    }

    /**
     * Get props using ClassParam behavior (snake_case conversion like QueryProp)
     */
    private function getPropsForClassParam(string $varName, array $query): mixed
    {
        if (isset($query[$varName])) {
            return $query[$varName];
        }

        // try snake_case variable name (ClassParam compatible)
        $snakeName = ltrim(strtolower((string) preg_replace('/[A-Z]/', '_\0', $varName)), '_');
        if (isset($query[$snakeName])) {
            return $query[$snakeName];
        }

        if ($this->isDefaultAvailable) {
            return $this->defaultValue;
        }

        throw new ParameterException($varName);
    }
}
