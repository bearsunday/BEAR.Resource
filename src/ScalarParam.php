<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Di\Di\Named;
use Ray\Di\Di\Qualifier;
use Ray\Di\InjectorInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

use function array_shift;
use function array_unshift;
use function assert;
use function class_exists;

/**
 * @psalm-type DependencyMeta = array{0:class-string|'', 1:string}
 * @psalm-type DependencyMetas = list<DependencyMeta>
 */
final class ScalarParam implements ParamInterface
{
    /** @var DependencyMetas */
    private array $dependenciesMetas;
    private QueryProp $queryProp;

    /** @param class-string $typeName */
    public function __construct(
        private string $typeName,
    ) {
        // Retrieve the dependency metadata to construct the object later
        $this->dependenciesMetas = $this->getDependenciesMetas();
        $this->queryProp = new QueryProp();
    }

    /**
     * {@inheritDoc}
     *
     * @param array<string, mixed> $query
     *
     * @return object
     */
    public function __invoke(string $varName, array $query, InjectorInterface $injector): object
    {
        /** @psalm-suppress MixedAssignment */
        $arg1 = $this->queryProp->getProp($varName, $query, $injector);
        $args = [];
        foreach ($this->dependenciesMetas as $meta) {
            /** @psalm-suppress MixedAssignment */
            [$className, $qualifier] = $meta;
            /** @psalm-suppress MixedAssignment, ArgumentTypeCoercion, MixedArgument */
            $args[] = $injector->getInstance($className, $qualifier);
        }

        array_unshift($args, $arg1);

        /** @psalm-suppress InvalidStringClass, MixedMethodCall */
        return new $this->typeName(...$args);
    }

    /**
     * @return DependencyMetas
     *
     * @psalm-suppress MoreSpecificReturnType, LessSpecificReturnStatement
     */
    public function getDependenciesMetas(): array
    {
        $class = new ReflectionClass($this->typeName);
        $const = $class->getConstructor();
        assert($const instanceof ReflectionMethod, 'Expected class has a constructor');

        $args = $const->getParameters();
        $params = [];

        // Skip the first parameter (scalar value)
        array_shift($args);

        foreach ($args as $arg) {
            $type = $arg->getType();
            assert($type instanceof ReflectionNamedType || $type === null, 'Expected a named type or null');
            $typeName = $type ? $type->getName() : '';
            $attributes = $arg->getAttributes();
            foreach ($attributes as $attribute) {
                if ($attribute->getName() === Named::class) {
                    $named = $attribute->newInstance();
                    /** @var DependencyMeta $dependencyMeta */
                    $dependencyMeta = [$typeName, $named->value];
                    $params[] = $dependencyMeta;
                    continue;
                }

                $class = $attribute->getName();
                assert(class_exists($class));
                $attributeReflection = new ReflectionClass($class);
                // Check if this attribute has the Qualifier attribute
                $qualifierAttributes = $attributeReflection->getAttributes(Qualifier::class);
                if (! $qualifierAttributes) {
                    continue;
                }

                /** @var DependencyMeta $dependencyMeta */
                $dependencyMeta = [$typeName, $attribute->getName()];

                $params[] = $dependencyMeta;
            }

            $params[] = [$typeName, ''];
        }

        /** @var DependencyMetas $params */
        return $params;
    }
}
