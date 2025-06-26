<?php

declare(strict_types=1);

namespace BEAR\Resource\Input;

use BEAR\Resource\ParamInterface;
use Ray\Di\InjectorInterface;
use ReflectionNamedType;
use ReflectionParameter;

final class InputParam implements ParamInterface
{
    private readonly string $type;
    private readonly bool $isDefaultAvailable;
    private readonly mixed $defaultValue;
    private readonly InputParamFactory $factory;

    public function __construct(
        ReflectionNamedType $type,
        private readonly ReflectionParameter $parameter,
    ) {
        $this->type = $type->getName();
        $this->isDefaultAvailable = $parameter->isDefaultValueAvailable();
        $this->defaultValue = $this->isDefaultAvailable ? $parameter->getDefaultValue() : null;
        $this->factory = new InputParamFactory(
            new InputParamEnumHandler(),
            new InputParamObjectHandler(),
        );
    }

    /**
     * {@inheritDoc}
     */

    /** @param array<string, mixed> $query */
    public function __invoke(string $varName, array $query, InjectorInterface $injector): mixed
    {
        return $this->factory->create(
            $this->type,
            $varName,
            $query,
            $injector,
            $this->parameter,
            $this->isDefaultAvailable,
            $this->defaultValue,
        );
    }
}
