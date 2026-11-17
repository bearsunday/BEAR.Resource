<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\InputQuery\Attribute\Input;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

use function array_unique;
use function array_values;
use function assert;
use function in_array;
use function is_array;
use function is_string;
use function method_exists;

/**
 * @internal
 * @psalm-import-type OptionsResponse from Types
 * @psalm-import-type InsMap from Types
 * @psalm-import-type ParameterMetadata from Types
 * @psalm-import-type ParametersMap from Types
 * @psalm-import-type RequiredParameterList from Types
 * @psalm-import-type ReflectionParameterList from Types
 */
final class OptionsMethodRequest
{
    /**
     * @param ParametersMap $paramDoc
     * @param InsMap        $ins
     *
     * @return OptionsResponse
     */
    public function __invoke(ReflectionMethod $method, array $paramDoc, array $ins): array
    {
        return $this->getParamMetas($method->getParameters(), $paramDoc, $ins);
    }

    /**
     * @param ParametersMap $paramDoc
     *
     * @psalm-suppress RedundantCondition for BC
     */
    private function getParameterType(ReflectionParameter $parameter, array $paramDoc, string $name): string|null
    {
        /** @phpstan-ignore function.alreadyNarrowedType */
        $hasType = method_exists($parameter, 'getType') && $parameter->getType();
        if ($hasType) {
            return $this->getType($parameter);
        }

        return $paramDoc[$name]['type'] ?? null;
    }

    /**
     * @param ReflectionParameterList $parameters
     * @param ParametersMap           $paramDoc
     * @param InsMap                  $ins
     *
     * @return OptionsResponse
     */
    private function getParamMetas(array $parameters, array $paramDoc, array $ins): array
    {
        $expandedParameters = [];
        $expandedRequired = [];
        $expandedParamNames = [];

        foreach ($parameters as $parameter) {
            if ($this->processInputParameter($parameter, $expandedParameters, $expandedRequired, $expandedParamNames)) {
                continue;
            }

            $this->processRegularParameter($parameter, $paramDoc, $ins);
        }

        // Merge expanded parameters with regular parameters
        $paramDoc = $expandedParameters + $paramDoc;

        $required = $this->getRequiredWithoutExpandedParams($parameters, $expandedParamNames);
        // Merge expanded required with regular required
        if ($expandedRequired !== []) {
            $required = array_values(array_unique([...$expandedRequired, ...$required]));
        }

        return $this->setParamMetas($paramDoc, $required);
    }

    /**
     * Process #[Input] parameter and accumulate expanded data
     *
     * @param ParametersMap $expandedParameters
     * @param list<string>  $expandedRequired
     * @param list<string>  $expandedParamNames
     *
     * @return bool True if parameter was processed as Input parameter
     */
    private function processInputParameter(
        ReflectionParameter $parameter,
        array &$expandedParameters,
        array &$expandedRequired,
        array &$expandedParamNames,
    ): bool {
        $inputAttributes = $parameter->getAttributes(Input::class, ReflectionAttribute::IS_INSTANCEOF);
        if (! $inputAttributes) {
            return false;
        }

        $inputResult = $this->expandInputParameter($parameter);
        if ($inputResult === null) {
            return false;
        }

        [$inputParamDoc, $inputRequired] = $inputResult;
        $expandedParameters += $inputParamDoc;
        $expandedRequired = [...$expandedRequired, ...$inputRequired];
        $expandedParamNames[] = $parameter->name;

        return true;
    }

    /**
     * Process regular (non-Input) parameter
     *
     * @param ParametersMap $paramDoc
     * @param InsMap        $ins
     */
    private function processRegularParameter(ReflectionParameter $parameter, array &$paramDoc, array $ins): void
    {
        $name = $parameter->name;

        if (isset($ins[$name])) {
            $paramDoc[$name]['in'] = $ins[$name];
        }

        if (! isset($paramDoc[$name])) {
            $paramDoc[$name] = [];
        }

        $this->setParameterType($paramDoc, $parameter);
        $this->setParameterDefault($paramDoc, $parameter);
    }

    /**
     * Expand #[Input] parameter to its constructor properties
     *
     * @return array{0: ParametersMap, 1: RequiredParameterList}|null
     */
    private function expandInputParameter(ReflectionParameter $parameter): array|null
    {
        $constructor = $this->getConstructor($parameter);
        if ($constructor === null) {
            return null;
        }

        $paramDoc = [];
        $required = [];

        foreach ($constructor->getParameters() as $ctorParam) {
            $name = $ctorParam->getName();
            $paramDoc[$name] = $this->buildConstructorParamDoc($ctorParam);

            if ($ctorParam->isOptional()) {
                continue;
            }

            $required[] = $name;
        }

        return [$paramDoc, $required];
    }

    /**
     * Get constructor from parameter type if it's a class with constructor
     */
    private function getConstructor(ReflectionParameter $parameter): ReflectionMethod|null
    {
        $type = $parameter->getType();
        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return null;
        }

        $refClass = new ReflectionClass($type->getName());

        return $refClass->getConstructor();
    }

    /**
     * Build parameter documentation for a constructor parameter
     *
     * @return array<string, string>
     */
    private function buildConstructorParamDoc(ReflectionParameter $ctorParam): array
    {
        $doc = [];

        $typeName = $this->getConstructorParamType($ctorParam);
        if ($typeName !== null) {
            $doc['type'] = $typeName;
        }

        $default = $this->getDefaultValueString($ctorParam);
        if ($default !== null) {
            $doc['default'] = $default;
        }

        return $doc;
    }

    /**
     * Get type name from constructor parameter
     */
    private function getConstructorParamType(ReflectionParameter $ctorParam): string|null
    {
        $ctorType = $ctorParam->getType();
        if (! $ctorType instanceof ReflectionNamedType) {
            return null;
        }

        $typeName = $ctorType->getName();

        return $typeName === 'int' ? 'integer' : $typeName;
    }

    /**
     * Get default value as string representation
     */
    private function getDefaultValueString(ReflectionParameter $param): string|null
    {
        if (! $param->isDefaultValueAvailable() || $param->getDefaultValue() === null) {
            return null;
        }

        $default = $param->getDefaultValue();

        return is_array($default) ? '[]' : (string) $default;
    }

    /**
     * Get required parameters excluding successfully expanded #[Input] parameters
     *
     * @param ReflectionParameterList $parameters
     * @param list<string>            $expandedParamNames Names of parameters that were expanded
     *
     * @return RequiredParameterList
     */
    private function getRequiredWithoutExpandedParams(array $parameters, array $expandedParamNames): array
    {
        $required = [];
        foreach ($parameters as $parameter) {
            if ($parameter->isOptional()) {
                continue;
            }

            // Skip parameters that were successfully expanded
            if (in_array($parameter->name, $expandedParamNames, true)) {
                continue;
            }

            $required[] = $parameter->name;
        }

        return $required;
    }

    /**
     * Set parameter default value if available
     *
     * @param ParametersMap $paramDoc
     */
    private function setParameterDefault(array &$paramDoc, ReflectionParameter $parameter): void
    {
        if (! $parameter->isDefaultValueAvailable() || $parameter->getDefaultValue() === null) {
            return;
        }

        $default = $parameter->getDefaultValue();
        $paramDoc[$parameter->name]['default'] = is_array($default) ? '[]' : (string) $default;
    }

    /**
     * Set parameter type from reflection
     *
     * @param ParametersMap $paramDoc
     */
    private function setParameterType(array &$paramDoc, ReflectionParameter $parameter): void
    {
        $type = $this->getParameterType($parameter, $paramDoc, $parameter->name);
        if (! is_string($type)) {
            return;
        }

        // Override type parameter by reflection over phpdoc param type
        $paramDoc[$parameter->name]['type'] = $type;
    }

    private function getType(ReflectionParameter $parameter): string
    {
        $namedType = $parameter->getType();
        assert($namedType instanceof ReflectionNamedType);
        $type = $namedType->getName();
        if ($type === 'int') {
            $type = 'integer';
        }

        return $type;
    }

    /**
     * @param ParametersMap         $paramDoc
     * @param RequiredParameterList $required
     *
     * @return OptionsResponse
     */
    private function setParamMetas(array $paramDoc, array $required): array
    {
        $paramMetas = [];
        if ($paramDoc !== []) {
            $paramMetas['parameters'] = $paramDoc;
        }

        if ($required !== []) {
            $paramMetas['required'] = $required;
        }

        return $paramMetas;
    }
}
