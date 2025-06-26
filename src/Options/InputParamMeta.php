<?php

declare(strict_types=1);

namespace BEAR\Resource\Options;

use BEAR\Resource\Input\InputAttributeIterator;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

use function array_merge;
use function assert;
use function class_exists;
use function in_array;

/**
 * Extracts metadata from Input attribute parameters
 */
final class InputParamMeta implements InputParamMetaInterface
{
    private InputAttributeIterator $inputIterator;
    private OptionsMethodDocBolck $docBlock;
    private OptionsMethodRequest $methodRequest;

    public function __construct()
    {
        $this->inputIterator = new InputAttributeIterator();
        $this->docBlock = new OptionsMethodDocBolck();
        $this->methodRequest = new OptionsMethodRequest();
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
            assert($type instanceof ReflectionNamedType);

            $className = $type->getName();
            assert(class_exists($className));

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
        assert($constructor instanceof ReflectionMethod, 'Input class must have a constructor');

        // Use OptionsMethodRequest to handle parameter processing
        [, $constructorParamDocs] = ($this->docBlock)($constructor);
        $paramMeta = ($this->methodRequest)($constructor, $constructorParamDocs, []);

        $parameters = $paramMeta['parameters'] ?? [];
        $required = $paramMeta['required'] ?? [];

        // Separate regular parameters and nested Input parameters
        $finalParameters = [];
        $finalRequired = [];

        foreach ($constructor->getParameters() as $param) {
            $paramName = $param->getName();

            if ($this->isNestedInputParameter($param)) {
                $nestedDescription = $constructorParamDocs[$paramName]['description'] ?? null;
                [$nestedParams, $nestedRequired] = $this->processNestedParameter(
                    $param,
                    $nestedDescription,
                );
                $finalParameters = array_merge($finalParameters, $nestedParams);
                $finalRequired = array_merge($finalRequired, $nestedRequired);
            } elseif (isset($parameters[$paramName])) {
                // Add group information to regular parameters
                $parameters[$paramName]['group'] = $group;
                if ($groupDescription) {
                    $parameters[$paramName]['group_description'] = $groupDescription;
                }

                $finalParameters[$paramName] = $parameters[$paramName];

                if (in_array($paramName, $required, true)) {
                    $finalRequired[] = $paramName;
                }
            }
        }

        return [$finalParameters, $finalRequired];
    }

    private function isNestedInputParameter(ReflectionParameter $param): bool
    {
        return $this->inputIterator->getInputAttribute($param) !== null;
    }

    /** @return array{0: array<string, array<string, mixed>>, 1: array<int, string>} */
    private function processNestedParameter(
        ReflectionParameter $param,
        string|null $nestedDescription,
    ): array {
        $type = $param->getType();
        assert($type instanceof ReflectionNamedType && class_exists($type->getName()));

        return $this->flattenInputClass(
            $type->getName(),
            $param->getName(),
            $nestedDescription,
        );
    }
}
