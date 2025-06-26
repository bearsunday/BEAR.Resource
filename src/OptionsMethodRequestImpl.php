<?php

declare(strict_types=1);

namespace BEAR\Resource;

use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

use function array_key_exists;
use function is_bool;
use function is_numeric;
use function is_string;

/**
 * Extracts parameter metadata from resource methods for OPTIONS responses
 */
final class OptionsMethodRequestImpl
{
    /**
     * Extract parameter metadata from method
     *
     * @param ReflectionMethod                                         $method
     * @param array<string, array{type: string, description?: string}> $paramDoc Parameter documentation from phpdoc
     * @param array<string, string>                                    $ins      Parameter input sources (query, formData, etc)
     *
     * @return array{parameters?: array<string, array<string, mixed>>, required?: array<int, string>}
     */
    public function __invoke(ReflectionMethod $method, array $paramDoc, array $ins): array
    {
        $parameters = $method->getParameters();
        if (! $parameters) {
            return [];
        }

        $paramMetas = $this->getParamMetas($parameters, $paramDoc, $ins);
        if (! $paramMetas) {
            return [];
        }

        $required = $this->getRequired($parameters);

        $result = ['parameters' => $paramMetas];
        if ($required) {
            $result['required'] = $required;
        }

        return $result;
    }

    /**
     * @param ReflectionParameter[]                                    $parameters
     * @param array<string, array{type: string, description?: string}> $paramDoc
     * @param array<string, string>                                    $ins
     *
     * @return array<string, array<string, mixed>>
     */
    private function getParamMetas(array $parameters, array $paramDoc, array $ins): array
    {
        $paramMetas = [];
        foreach ($parameters as $parameter) {
            $paramName = $parameter->getName();
            $paramMetas[$paramName] = $this->paramType($parameter, $paramDoc);
            $this->paramDefault($paramMetas[$paramName], $parameter);
            $this->paramIn($paramMetas[$paramName], $paramName, $ins);
        }

        return $paramMetas;
    }

    /**
     * @param ReflectionParameter[] $parameters
     *
     * @return array<int, string>
     */
    private function getRequired(array $parameters): array
    {
        $required = [];
        foreach ($parameters as $parameter) {
            if ($parameter->isDefaultValueAvailable() || $parameter->allowsNull()) {
                continue;
            }

            $required[] = $parameter->getName();
        }

        return $required;
    }

    /**
     * @param ReflectionParameter                                      $parameter
     * @param array<string, array{type: string, description?: string}> $paramDoc
     *
     * @return array<string, mixed>
     */
    private function paramType(ReflectionParameter $parameter, array $paramDoc): array
    {
        $type = $parameter->getType();
        $name = $parameter->getName();
        $hasDoc = isset($paramDoc[$name]);

        if (! $hasDoc && ! $type) {
            return [];
        }

        $paramMeta = [];
        if ($type instanceof ReflectionNamedType) {
            $typeName = $type->getName();
            if ($typeName === 'int') {
                $typeName = 'integer';
            }

            $paramMeta['type'] = $typeName;
        }

        if ($hasDoc) {
            if (isset($paramDoc[$name]['type']) && ! isset($paramMeta['type'])) {
                $paramMeta['type'] = $paramDoc[$name]['type'];
            }

            if (isset($paramDoc[$name]['description'])) {
                $paramMeta['description'] = $paramDoc[$name]['description'];
            }
        }

        return $paramMeta;
    }

    /**
     * @param array<string, mixed> $paramMeta
     * @param ReflectionParameter  $parameter
     */
    private function paramDefault(array &$paramMeta, ReflectionParameter $parameter): void
    {
        if (! $parameter->isDefaultValueAvailable()) {
            return;
        }

        $defaultValue = $parameter->getDefaultValue();
        if (is_string($defaultValue)) {
            $paramMeta['default'] = $defaultValue;
        } elseif (is_bool($defaultValue)) {
            $paramMeta['default'] = $defaultValue ? 'true' : 'false';
        } elseif (is_numeric($defaultValue)) {
            $paramMeta['default'] = (string) $defaultValue;
        }
    }

    /**
     * @param array<string, mixed>  $paramMeta
     * @param string                $paramName
     * @param array<string, string> $ins
     */
    private function paramIn(array &$paramMeta, string $paramName, array $ins): void
    {
        if (! array_key_exists($paramName, $ins)) {
            return;
        }

        $paramMeta['in'] = $ins[$paramName];
    }
}
