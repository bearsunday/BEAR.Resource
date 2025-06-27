<?php

declare(strict_types=1);

namespace BEAR\Resource\Input;

use BEAR\Resource\Exception\InputClassParameterException;
use BEAR\Resource\Exception\ParameterException;
use InvalidArgumentException;
use Ray\Di\InjectorInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use Throwable;

use function is_iterable;
use function ltrim;
use function preg_replace;
use function sprintf;
use function strtolower;

final class InputParamObjectHandler
{
    public function __construct(
        private readonly InputAttributeIterator $inputIterator,
    ) {
    }

    /**
     * Creates an object instance without calling its constructor
     *
     * This method instantiates an object and directly assigns values to its properties
     * without invoking the constructor. This approach is used to maintain backward
     * compatibility for legacy code that expects objects to be created this way.
     *
     * @param array<string, mixed> $query
     */
    public function createWithoutConstructor(
        string $type,
        string $varName,
        array $query,
        bool $isDefaultAvailable,
        mixed $defaultValue,
    ): mixed {
        $props = $this->getPropsForClassParam($varName, $query, $isDefaultAvailable, $defaultValue);

        if (! is_iterable($props)) {
            if ($isDefaultAvailable) {
                return $defaultValue;
            }

            throw new ParameterException("Expected array data for {$type}");
        }

        /** @var class-string $type */
        /** @psalm-suppress MixedMethodCall, InvalidStringClass */
        $obj = new $type();
        /** @psalm-suppress MixedAssignment */
        foreach ($props as $propName => $propValue) {
            $obj->{$propName} = $propValue;
        }

        return $obj;
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<mixed>
     */
    public function getConstructorArgs(ReflectionMethod $constructor, array $query, InjectorInterface $injector, string $type = ''): array
    {
        /** @var list<mixed> $constructorArgs */
        $constructorArgs = [];

        foreach ($constructor->getParameters() as $param) {
            $paramName = $param->getName();

            $inputAttr = $this->inputIterator->getInputAttribute($param);
            if ($inputAttr !== null) {
                $paramType = $param->getType();
                if ($paramType instanceof ReflectionNamedType) {
                    $nestedInputParam = new InputParam($paramType, $param);
                    /** @psalm-suppress MixedArgumentTypeCoercion, MixedAssignment */
                    $constructorArgs[] = $nestedInputParam($paramName, $query, $injector);
                    continue;
                }
            }

            /** @psalm-suppress MixedArgumentTypeCoercion */
            $paramValue = $this->getParamValue($paramName, $query);
            if ($paramValue !== null) {
                /** @psalm-suppress MixedAssignment */
                $constructorArgs[] = $paramValue;
                continue;
            }

            if ($param->isDefaultValueAvailable()) {
                /** @psalm-suppress MixedAssignment */
                $constructorArgs[] = $param->getDefaultValue();
                continue;
            }

            $message = sprintf('paramName:%s: class:%s', $paramName, $type);

            throw new InputClassParameterException($message);
        }

        return $constructorArgs;
    }

    /**
     * @param array<string, mixed>    $data
     * @param ReflectionClass<object> $refClass
     */
    public function newInstance(
        ReflectionMethod $constructor,
        array $data,
        InjectorInterface $injector,
        string $key,
        ReflectionClass $refClass,
        string $type,
    ): object {
        try {
            /** @var list<mixed> $constructorArgs */
            $constructorArgs = [];

            foreach ($constructor->getParameters() as $param) {
                $paramName = $param->getName();

                $inputAttr = $this->inputIterator->getInputAttribute($param);
                if ($inputAttr !== null) {
                    $paramType = $param->getType();
                    if ($paramType instanceof ReflectionNamedType) {
                        $nestedInputParam = new InputParam($paramType, $param);
                        /** @psalm-suppress MixedArgumentTypeCoercion, MixedAssignment */
                        $constructorArgs[] = $nestedInputParam($paramName, $data, $injector);
                        continue;
                    }
                }

                if (isset($data[$paramName])) {
                    /** @psalm-suppress MixedAssignment */
                    $constructorArgs[] = $data[$paramName];
                    continue;
                }

                if ($param->isDefaultValueAvailable()) {
                    /** @psalm-suppress MixedAssignment */
                    $constructorArgs[] = $param->getDefaultValue();
                    continue;
                }

                throw new ParameterException("Required parameter '{$paramName}' not found in key '{$key}' for {$type}");
            }

            /** @psalm-suppress MixedArgumentTypeCoercion */
            return $refClass->newInstanceArgs($constructorArgs);
        } catch (Throwable $e) {
            if ($e instanceof InvalidArgumentException) {
                throw $e;
            }

            throw new ParameterException("Failed to create {$type} from key '{$key}': " . $e->getMessage(), 0, $e);
        }
    }

    /** @param array<string, mixed> $query */
    private function getParamValue(string $paramName, array $query): mixed
    {
        if (isset($query[$paramName])) {
            return $query[$paramName];
        }

        $kebabName = ltrim(strtolower((string) preg_replace('/[A-Z]/', '-\0', $paramName)), '-');
        if (isset($query[$kebabName])) {
            return $query[$kebabName];
        }

        return null;
    }

    /** @param array<string, mixed> $query */
    private function getPropsForClassParam(
        string $varName,
        array $query,
        bool $isDefaultAvailable,
        mixed $defaultValue,
    ): mixed {
        if (isset($query[$varName])) {
            return $query[$varName];
        }

        $snakeName = ltrim(strtolower((string) preg_replace('/[A-Z]/', '_\0', $varName)), '_');
        if (isset($query[$snakeName])) {
            return $query[$snakeName];
        }

        if ($isDefaultAvailable) {
            return $defaultValue;
        }

        throw new ParameterException($varName);
    }
}
