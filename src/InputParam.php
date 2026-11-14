<?php

declare(strict_types=1);

namespace BEAR\Resource;

use InvalidArgumentException;
use Override;
use Ray\Di\InjectorInterface;
use Ray\InputQuery\InputQueryInterface;
use ReflectionNamedType;
use ReflectionParameter;

use function array_key_exists;
use function assert;
use function class_exists;

/** @psalm-import-type Query from Types */
final readonly class InputParam implements ParamInterface
{
    /**
     * @param InputQueryInterface<object> $inputQuery
     *
     * @psalm-pure
     */
    public function __construct(
        private InputQueryInterface $inputQuery,
        private ReflectionParameter $parameter,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException When input query rejects request parameters or a required built-in parameter is missing.
     *
     * @psalm-taint-source input
     */
    #[Override]
    public function __invoke(string $varName, array $query, InjectorInterface $injector)
    {
        $type = $this->parameter->getType();
        if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
            $inputClass = $type->getName();
            assert(class_exists($inputClass));

            return $this->inputQuery->newInstance($inputClass, $query);
        }

        // For built-in types, handle missing values explicitly
        if (array_key_exists($varName, $query)) {
            return $query[$varName];
        }

        $type = $this->parameter->getType();
        $isRequired = true;
        if ($type instanceof ReflectionNamedType && $type->allowsNull()) {
            $isRequired = false;
        }

        if ($this->parameter->isDefaultValueAvailable()) {
            $isRequired = false;
        }

        if ($isRequired) {
            throw new InvalidArgumentException("Missing required parameter: {$varName}");
        }

        return null;
    }
}
