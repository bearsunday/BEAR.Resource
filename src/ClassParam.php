<?php

namespace BEAR\Resource;

use BackedEnum;
use BEAR\Resource\Exception\ParameterException;
use Ray\Di\InjectorInterface;
use ReflectionClass;
use ReflectionEnum;
use ReflectionNamedType;
use ReflectionParameter;

use function assert;
use function class_exists;
use function enum_exists;
use function is_a;
use function is_iterable;
use function ltrim;
use function preg_replace;
use function strtolower;

use const PHP_VERSION_ID;

final class ClassParam implements ParamInterface
{
    private string $type;
    private bool $isDefaultAvailable;
    private mixed $defaultValue;

    public function __construct(
        ReflectionNamedType $type,
        ReflectionParameter $parameter,
    ) {
        $this->type = $type->getName();
        $this->isDefaultAvailable = $parameter->isDefaultValueAvailable();
        if (! $this->isDefaultAvailable) {
            return;
        }

        $this->defaultValue = $parameter->getDefaultValue();
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(string $varName, array $query, InjectorInterface $injector)
    {
        try {
            /** @psalm-suppress MixedAssignment */
            $props = $this->getProps($varName, $query, $injector);
        } catch (ParameterException $e) {
            if ($this->isDefaultAvailable) {
                return $this->defaultValue;
            }

            throw $e;
        }

        assert(class_exists($this->type));
        $refClass = (new ReflectionClass($this->type));

        if (PHP_VERSION_ID >= 80100 && $refClass->isEnum()) {
            return $this->enum($this->type, $props);
        }

        assert(is_iterable($props));

        $hasConstructor = (bool) $refClass->getConstructor();
        if ($hasConstructor) {
            /** @psalm-suppress MixedMethodCall */
            return new $this->type(...$props);
        }

        /** @psalm-suppress MixedMethodCall */
        $obj = new $this->type();
        /** @psalm-suppress MixedAssignment */
        foreach ($props as $propName => $propValue) {
            $obj->{$propName} = $propValue;
        }

        return $obj;
    }

    /** @param array<string, mixed> $query */
    private function getProps(string $varName, array $query, InjectorInterface $injector): mixed
    {
        if (isset($query[$varName])) {
            return $query[$varName];
        }

        // try camelCase variable name
        $snakeName = ltrim(strtolower((string) preg_replace('/[A-Z]/', '_\0', $varName)), '_');
        if (isset($query[$snakeName])) {
            return $query[$snakeName];
        }

        unset($injector);

        throw new ParameterException($varName);
    }

    /** @psalm-suppress MixedArgument */
    private function enum(string $type, mixed $props): mixed
    {
        $refEnum = new ReflectionEnum($type);
        assert(enum_exists($type));

        if (! $refEnum->isBacked()) {
            throw new NotBackedEnumException($type);
        }

        assert(is_a($type, BackedEnum::class, true));

        return $type::from($props); // @phpstan-ignore-line
    }
}
