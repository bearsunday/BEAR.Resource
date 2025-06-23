<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BackedEnum;
use BEAR\Resource\Exception\ParameterEnumTypeException;
use BEAR\Resource\Exception\ParameterException;
use BEAR\Resource\Exception\ParameterInvalidEnumException;
use Doctrine\Common\Annotations\Reader;
use Ray\Di\InjectorInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionEnum;
use ReflectionNamedType;
use ReflectionParameter;
use UnitEnum;

use function array_pop;
use function array_search;
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
    /** @var array<int, mixed> */
    private array $dependencies;

    public function __construct(
        private ReflectionNamedType $type,
        private ReflectionParameter $parameter,
        private InjectorInterface $injector,
    ) {
        // Resolve dependencies at construction time
        $this->dependencies = $this->resolveDependencies($injector);
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(string $varName, array $query, InjectorInterface $injector)
    {
        try {
            /** @psalm-suppress MixedAssignment */
            $arg1 = $this->getProp($varName, $query, $injector);
        } catch (ParameterException $e) {
            if ($this->parameter->isDefaultValueAvailable()) {
                return $this->parameter->getDefaultValue();
            }

            throw $e;
        }

        $args = $this->dependencies;
        array_unshift($args, $arg1);

        return new ($this->type->getName())(...$args);
    }

    public function resolveDependencies(InjectorInterface $injector): array
    {
        $class = new ReflectionClass($this->type->getName());
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
            if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
                // Check for qualifier attributes
                $qualifierAttribute = null;
                $attributes = $arg->getAttributes();
                foreach ($attributes as $attribute) {
                    $attributeInstance = $attribute->newInstance();
                    $attributeReflection = new ReflectionClass($attribute->getName());
                    // Check if this attribute has the Qualifier attribute
                    $qualifierAttributes = $attributeReflection->getAttributes('Ray\Di\Di\Qualifier');
                    if ($qualifierAttributes) {
                        $qualifierAttribute = $attribute->getName();
                        break;
                    }
                }

                if ($qualifierAttribute) {
                    // Get instance with qualifier
                    $params[] = $injector->getInstance($type->getName(), $qualifierAttribute);
                } else {
                    // Get instance without qualifier
                    $params[] = $injector->getInstance($type->getName());
                }
            } elseif ($arg->isDefaultValueAvailable()) {
                $params[] = $arg->getDefaultValue();
            }
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
