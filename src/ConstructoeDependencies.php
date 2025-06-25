<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Di\Di\Named;
use Ray\Di\Di\Qualifier;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

use function array_shift;
use function assert;
use function class_exists;

/**
 * @psalm-type DependencyMeta = array{0:class-string|'', 1:string}
 * @psalm-type DependencyMetas = list<DependencyMeta>
 */
final class ConstructoeDependencies
{
    /**
     * @param ReflectionClass<object> $class
     *
     * @return array<string, DependencyMeta>
     *
     * @psalm-suppress MoreSpecificReturnType, LessSpecificReturnStatement
     */
    public function __invoke(ReflectionClass $class): array
    {
        $const = $class->getConstructor();
        if ($const === null) {
            return [];
        }

        assert($const instanceof ReflectionMethod, 'The class must have a constructor');

        $args = $const->getParameters();
        $params = [];

        // Skip the first parameter (scalar value)
        array_shift($args);

        foreach ($args as $arg) {
            $type = $arg->getType();
            $typeName = $type instanceof ReflectionNamedType ? $type->getName() : '';
            $attributes = $arg->getAttributes();
            $named = '';
            foreach ($attributes as $attribute) {
                if ($attribute->getName() === Named::class) {
                    $named = $attribute->newInstance()->value;
                    break;
                }

                $class = $attribute->getName();
                assert(class_exists($class));
                $attributeReflection = new ReflectionClass($class);
                // Check if this attribute has the Qualifier attribute
                $qualifierAttributes = $attributeReflection->getAttributes(Qualifier::class);
                if (! $qualifierAttributes) {
                    continue;
                }

                $named = $attribute->getName();
            }

            $params[$arg->name] = [$typeName, $named];
        }

        /** @var DependencyMetas $params */
        return $params;
    }
}
