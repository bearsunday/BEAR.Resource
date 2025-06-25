<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Input;
use BEAR\Resource\Exception\ParameterException;
use InvalidArgumentException;
use Ray\Di\InjectorInterface;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use Throwable;

use function assert;
use function class_exists;
use function count;

/**
 * @psalm-type DependencyMeta = array{0:class-string|'', 1:string}
 * @psalm-type DependencyMetas = array<string, DependencyMeta>
 */
final class InputParam implements ParamInterface
{
    private readonly string $type;
    private readonly bool $isDefaultAvailable;
    private readonly mixed $defaultValue;

    /** @var DependencyMetas */
    private array $dependenciesMetas = [];

    public function __construct(
        ReflectionNamedType $type,
        ReflectionParameter $parameter,
    ) {
        $this->type = $type->getName();
        $this->isDefaultAvailable = $parameter->isDefaultValueAvailable();

        if ($this->isDefaultAvailable) {
            $this->defaultValue = $parameter->getDefaultValue();
        }

        // Initialize dependency metadata for constructor injection
        $this->dependenciesMetas = (new ConstructoeDependencies())(new ReflectionClass($this->type));
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(string $varName, array $query, InjectorInterface $injector)
    {
        assert(class_exists($this->type));
        $refClass = new ReflectionClass($this->type);

        $constructor = $refClass->getConstructor();
        if ($constructor === null) {
            throw new ParameterException("Class {$this->type} must have a constructor");
        }

        try {
            $constructorArgs = [];

            foreach ($constructor->getParameters() as $param) {
                $paramName = $param->getName();

                // Check if parameter has #[Input] attribute for nested input objects
                if ($this->hasInputAttribute($param)) {
                    $paramType = $param->getType();
                    if ($paramType instanceof ReflectionNamedType) {
                        $nestedInputParam = new InputParam($paramType, $param);
                        $constructorArgs[] = $nestedInputParam($paramName, $query, $injector);
                        continue;
                    }
                }

                // Check if parameter needs DI (skip built-in types)
                if (isset($this->dependenciesMetas[$paramName])) {
                    [$className, $qualifier] = $this->dependenciesMetas[$paramName];
                    if (! empty($className) && class_exists($className)) {
                        $constructorArgs[] = $injector->getInstance($className, $qualifier);
                        continue;
                    }
                }

                // Use query parameter if available
                if (isset($query[$paramName])) {
                    $constructorArgs[] = $query[$paramName];
                    continue;
                }

                // Use default value if available
                if ($param->isDefaultValueAvailable()) {
                    $constructorArgs[] = $param->getDefaultValue();
                    continue;
                }

                // Check if parameter is nullable
                if ($param->allowsNull()) {
                    $constructorArgs[] = null;
                    continue;
                }

                throw new ParameterException("Required parameter '{$paramName}' not found for {$this->type}");
            }

            return $refClass->newInstanceArgs($constructorArgs);
        } catch (Throwable $e) {
            if ($this->isDefaultAvailable) {
                return $this->defaultValue;
            }

            // Re-throw validation exceptions directly
            if ($e instanceof InvalidArgumentException) {
                throw $e;
            }

            throw new ParameterException("Failed to create {$this->type}: " . $e->getMessage(), 0, $e);
        }
    }

    private function hasInputAttribute(ReflectionParameter $param): bool
    {
        $attributes = $param->getAttributes(Input::class);

        return count($attributes) > 0;
    }
}
