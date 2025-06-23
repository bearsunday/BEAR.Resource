<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterException;
use Ray\Di\Di\Named;
use Ray\Di\Di\Qualifier;
use Ray\Di\InjectorInterface;
use ReflectionClass;
use ReflectionParameter;

use function array_shift;
use function array_unshift;
use function ltrim;
use function preg_replace;
use function strtolower;

final class ScalarParam implements ParamInterface
{
    /** @var array<int, mixed> */
    private array $dependenciesMetas;

    public function __construct(
        private string $typeName,
        private ReflectionParameter $parameter,
    ) {
        // Retrieve the dependency metadata to construct the object later
        $this->dependenciesMetas = $this->getDependenciesMetas();
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(string $varName, array $query, InjectorInterface $injector)
    {
        $arg1 = $this->getProp($varName, $query, $injector);
        $args = [];
        foreach ($this->dependenciesMetas as $meta) {
            [$className, $qualifier] = $meta;
            $args[] = $injector->getInstance($className, $qualifier);
        }

        array_unshift($args, $arg1);

        return new ($this->typeName)(...$args);
    }

    public function getDependenciesMetas(): array
    {
        $class = new ReflectionClass($this->typeName);
        $const = $class->getConstructor();
        if (! $const) {
            return []; // No constructor to resolve
        }

        $args = $const->getParameters();
        $params = [];

        // Skip the first parameter (scalar value)
        array_shift($args);

        foreach ($args as $arg) {
            $type = $arg->getType();
            $typeName = $type ? $type->getName() : '';
            $attributes = $arg->getAttributes();
            foreach ($attributes as $attribute) {
                if ($attribute->getName() === Named::class) {
                    $named = $attribute->newInstance();
                    $params[] = [$typeName, $named->value];
                    continue;
                }

                $attributeReflection = new ReflectionClass($attribute->getName());
                // Check if this attribute has the Qualifier attribute
                $qualifierAttributes = $attributeReflection->getAttributes(Qualifier::class);
                if (! $qualifierAttributes) {
                    continue;
                }

                $params[] = [$typeName, $attribute->getName()];
            }

            $params[] = [$typeName, ''];
        }

        return $params;
    }

    /** @param array<string, mixed> $query */
    private function getProp(string $varName, array $query, InjectorInterface $injector): mixed
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
