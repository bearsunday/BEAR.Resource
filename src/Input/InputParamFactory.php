<?php

declare(strict_types=1);

namespace BEAR\Resource\Input;

use BEAR\Resource\Annotation\Input;
use BEAR\Resource\Exception\InputClassCreateException;
use BEAR\Resource\Exception\ParameterException;
use InvalidArgumentException;
use Ray\Di\InjectorInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Throwable;

use function assert;
use function class_exists;
use function count;
use function enum_exists;
use function is_array;

final class InputParamFactory
{
    public function __construct(
        private readonly InputParamEnumHandler $enumHandler,
        private readonly InputParamObjectHandler $objectHandler,
    ) {
    }

    /** @param array<string, mixed> $query */
    public function create(
        string $type,
        string $varName,
        array $query,
        InjectorInterface $injector,
        ReflectionParameter $parameter,
        bool $isDefaultAvailable,
        mixed $defaultValue,
    ): mixed {
        /** @var class-string $type */
        /** @psalm-suppress MixedArgument, ArgumentTypeCoercion */
        assert(class_exists($type) || enum_exists($type));
        /** @psalm-suppress ArgumentTypeCoercion */
        $refClass = new ReflectionClass($type);

        // Handle enums
        if ($refClass->isEnum()) {
            return $this->enumHandler->createEnum($type, $varName, $query, $isDefaultAvailable, $defaultValue);
        }

        $constructor = $refClass->getConstructor();
        if ($constructor === null) {
            return $this->objectHandler->createWithoutConstructor($type, $varName, $query, $isDefaultAvailable, $defaultValue);
        }

        // Check if this parameter has #[Input] attribute
        $inputAttr = $this->getInputAttribute($parameter);

        // If no #[Input] attribute, use ClassParam behavior
        if ($inputAttr === null) {
            return $this->createFromStructuredData($refClass, $constructor, $varName, $query, $injector, $type, $isDefaultAvailable, $defaultValue);
        }

        // If #[Input] has key specified, use structured data approach
        if ($inputAttr->key !== null) {
            return $this->createFromStructuredData($refClass, $constructor, $inputAttr->key, $query, $injector, $type, $isDefaultAvailable, $defaultValue);
        }

        try {
            $constructorArgs = $this->objectHandler->getConstructorArgs($constructor, $query, $injector, $type);

            /** @psalm-suppress MixedArgumentTypeCoercion */
            return $refClass->newInstanceArgs($constructorArgs);
        } catch (Throwable $e) {
            if ($e instanceof InvalidArgumentException) {
                throw $e;
            }

            throw new InputClassCreateException($type, 0, $e);
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
     * @param ReflectionClass<object> $refClass
     * @param array<string, mixed>    $query
     */
    private function createFromStructuredData(
        ReflectionClass $refClass,
        ReflectionMethod $constructor,
        string $key,
        array $query,
        InjectorInterface $injector,
        string $type,
        bool $isDefaultAvailable,
        mixed $defaultValue,
    ): mixed {
        if (! isset($query[$key])) {
            if ($isDefaultAvailable) {
                return $defaultValue;
            }

            throw new ParameterException("Required key '{$key}' not found for {$type}");
        }

        $data = $query[$key];
        if (! is_array($data)) {
            throw new ParameterException("Data under key '{$key}' must be an array for {$type}");
        }

        /** @var array<string, mixed> $data */
        return $this->objectHandler->newInstance($constructor, $data, $injector, $key, $refClass, $type);
    }
}
