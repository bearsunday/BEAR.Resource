<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BackedEnum;
use BEAR\Resource\Exception\ParameterEnumTypeException;
use BEAR\Resource\Exception\ParameterException;
use BEAR\Resource\Exception\ParameterInvalidEnumException;
use Ray\Di\InjectorInterface;
use ReflectionEnum;
use ReflectionNamedType;
use ReflectionParameter;
use UnitEnum;

use function assert;
use function class_exists;
use function enum_exists;
use function is_a;
use function is_int;
use function is_string;
use function ltrim;
use function preg_replace;
use function strtolower;

final class ScalarParam implements ParamInterface
{
    private readonly string $type;
    private readonly bool $isDefaultAvailable;
    private readonly mixed $defaultValue; // @phpstan-ignore-line

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

        return new $this->type($props);
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

    /**
     * @param class-string $type
     *
     * @psalm-suppress MixedArgument
     */
    private function enum(string $type, mixed $props, string $varName): mixed
    {
        /** @var class-string<UnitEnum> $type */
        $refEnum = new ReflectionEnum($type);
        assert(enum_exists($type));

        if (! $refEnum->isBacked()) {
            throw new NotBackedEnumException($type);
        }

        assert(is_a($type, BackedEnum::class, true));
        if (! (is_int($props) || is_string($props))) {
            throw new ParameterEnumTypeException($varName);
        }

        /**  @psalm-suppress MixedAssignment */
        $value = $type::tryFrom($props);
        if ($value === null) {
            throw new ParameterInvalidEnumException($varName);
        }

        return $value;
    }
}
