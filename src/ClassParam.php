<?php

namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterException;
use Ray\Di\InjectorInterface;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;

use function assert;
use function class_exists;
use function ltrim;
use function preg_replace;
use function strtolower;

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
            $props = $this->getProps($varName, $query, $injector);
        } catch (ParameterException $e) {
            if ($this->isDefaultAvailable) {
                return $this->defaultValue;
            }

            throw $e;
        }

        assert(class_exists($this->type));
        $hasConstructor = (bool) (new ReflectionClass($this->type))->getConstructor();
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

    /**
     * @param array<string, array<string, mixed>> $query
     *
     * @return array<string, mixed>
     */
    private function getProps(string $varName, array $query, InjectorInterface $injector): array
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
}
