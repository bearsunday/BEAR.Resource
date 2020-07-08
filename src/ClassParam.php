<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterException;
use Ray\Di\InjectorInterface;
use ReflectionClass;
use ReflectionParameter;

use function assert;
use function class_exists;
use function ltrim;
use function preg_replace;
use function strtolower;

final class ClassParam implements ParamInterface
{
    /** @var string */
    private $class;

    /** @var bool */
    private $isDefaultAvailable;

    /** @var mixed */
    private $defaultValue;

    /**
     * @param ReflectionClass<ResourceObject> $class
     */
    public function __construct(ReflectionClass $class, ReflectionParameter $parameter)
    {
        $this->class = $class->name;
        $this->isDefaultAvailable = $parameter->isDefaultValueAvailable();
        if ($this->isDefaultAvailable) {
            $this->defaultValue = $parameter->getDefaultValue();
        }
    }

    /**
     * {@inheritdoc}
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

        assert(class_exists($this->class));
        /** @psalm-suppress MixedMethodCall */
        $obj = new $this->class();
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
