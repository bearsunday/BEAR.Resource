<?php

declare(strict_types=1);

namespace BEAR\Resource\Options;

use BEAR\Resource\InputAttributeIterator;
use BEAR\Resource\OptionsMethodDocBolck;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

use function array_merge;
use function class_exists;
use function is_array;
use function is_bool;
use function is_numeric;
use function is_string;

/**
 * Extracts metadata from Input attribute parameters
 */
final class InputParamMeta implements InputParamMetaInterface
{
    private InputAttributeIterator $inputIterator;
    private OptionsMethodDocBolck $docBlock;

    public function __construct()
    {
        $this->inputIterator = new InputAttributeIterator();
        $this->docBlock = new OptionsMethodDocBolck();
    }

    /**
     * {@inheritDoc}
     */
    public function get(ReflectionMethod $method): array
    {
        if (! $this->inputIterator->hasInputParameters($method)) {
            return [];
        }

        [, $methodParamDocs] = ($this->docBlock)($method);

        $allParameters = [];
        $allRequired = [];

        foreach (($this->inputIterator)($method) as $paramName => $param) {
            $type = $param->getType();
            if (! $type instanceof ReflectionNamedType) {
                continue;
            }

            $className = $type->getName();
            if (! class_exists($className)) {
                continue;
            }

            $groupDescription = $methodParamDocs[$paramName]['description'] ?? null;
            [$flatParams, $required] = $this->flattenInputClass(
                $className,
                $paramName,
                $groupDescription,
            );

            $allParameters = array_merge($allParameters, $flatParams);
            $allRequired = array_merge($allRequired, $required);
        }

        $result = [];
        if (! empty($allParameters)) {
            $result['parameters'] = $allParameters;
        }

        if (! empty($allRequired)) {
            $result['required'] = $allRequired;
        }

        return $result;
    }

    /**
     * Flatten Input class into query parameters
     *
     * @param class-string $className
     *
     * @return array{0: array<string, array<string, mixed>>, 1: array<int, string>}
     */
    private function flattenInputClass(
        string $className,
        string $group,
        string|null $groupDescription,
    ): array {
        $refClass = new ReflectionClass($className);
        $constructor = $refClass->getConstructor();

        if (! $constructor) {
            return [[], []];
        }

        // Get constructor documentation using OptionsMethodDocBolck
        // This already handles type conversion (int -> integer) and description extraction
        [, $constructorParamDocs] = ($this->docBlock)($constructor);

        $parameters = [];
        $required = [];

        foreach ($constructor->getParameters() as $param) {
            $paramName = $param->getName();

            // Check if this is a nested Input parameter
            if ($this->inputIterator->getInputAttribute($param) !== null) {
                $type = $param->getType();
                if ($type instanceof ReflectionNamedType && class_exists($type->getName())) {
                    $nestedDescription = null;
                    if (isset($constructorParamDocs[$paramName]['description'])) {
                        $nestedDescription = $constructorParamDocs[$paramName]['description'];
                    }

                    [$nestedParams, $nestedRequired] = $this->flattenInputClass(
                        $type->getName(),
                        $paramName,
                        $nestedDescription,
                    );

                    $parameters = array_merge($parameters, $nestedParams);
                    $required = array_merge($required, $nestedRequired);
                    continue;
                }
            }

            // Start with phpdoc information (already has type conversion)
            $paramMeta = [];
            if (isset($constructorParamDocs[$paramName])) {
                $paramMeta = $constructorParamDocs[$paramName];
            }

            // Override with reflection type if phpdoc doesn't have type
            if (! isset($paramMeta['type'])) {
                $type = $param->getType();
                if ($type instanceof ReflectionNamedType) {
                    $typeName = $type->getName();
                    // Apply same conversion as OptionsMethodDocBolck
                    $paramMeta['type'] = $typeName === 'int' ? 'integer' : $typeName;
                }
            }

            // Add default value
            if ($param->isDefaultValueAvailable()) {
                /** @var mixed $defaultValue */
                $defaultValue = $param->getDefaultValue();
                if (is_string($defaultValue)) {
                    $paramMeta['default'] = $defaultValue;
                } elseif (is_bool($defaultValue)) {
                    $paramMeta['default'] = $defaultValue ? 'true' : 'false';
                } elseif (is_numeric($defaultValue)) {
                    $paramMeta['default'] = (string) $defaultValue;
                } elseif (is_array($defaultValue)) {
                    $paramMeta['default'] = '[]';
                }
            }

            // Add group information
            $paramMeta['group'] = $group;
            if ($groupDescription) {
                $paramMeta['group_description'] = $groupDescription;
            }

            $parameters[$paramName] = $paramMeta;

            // Check if required
            if ($param->isDefaultValueAvailable() || $param->allowsNull()) {
                continue;
            }

            $required[] = $paramName;
        }

        return [$parameters, $required];
    }
}
