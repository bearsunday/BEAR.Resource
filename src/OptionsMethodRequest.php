<?php

declare(strict_types=1);

namespace BEAR\Resource;

use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

use function assert;
use function is_array;
use function is_string;
use function method_exists;

final class OptionsMethodRequest
{
    /**
     * Parameter #2 $paramMetas of method BEAR\Resource\OptionsMethodRequest::ignoreAnnotatedPrameter() expects array('parameters' => array<string, array('type' =>
     *
     * @param array<string, array{type: string, description?: string}> $paramDoc
     * @param array<string, string>                                    $ins
     *
     * @return array{parameters?: array<string, array{type?: string, description?: string, default?: string}>, required?: array<int, string>}
     */
    public function __invoke(ReflectionMethod $method, array $paramDoc, array $ins): array
    {
        return $this->getParamMetas($method->getParameters(), $paramDoc, $ins);
    }

    /**
     * @param array<string, array{type?: string, description?: string}> $paramDoc
     *
     * @psalm-suppress RedundantCondition for BC
     */
    private function getParameterType(ReflectionParameter $parameter, array $paramDoc, string $name): string|null
    {
        $hasType = method_exists($parameter, 'getType') && $parameter->getType();
        if ($hasType) {
            return $this->getType($parameter);
        }

        if (isset($paramDoc[$name]['type'])) {
            return $paramDoc[$name]['type'];
        }

        return null;
    }

    /**
     * @param array<ReflectionParameter>                               $parameters
     * @param array<string, array{type: string, description?: string}> $paramDoc
     * @param array<string, string>                                    $ins
     *
     * @return array{parameters?: array<string, array{type?: string}>, required?: array<int, string>}
     */
    private function getParamMetas(array $parameters, array $paramDoc, array $ins): array
    {
        foreach ($parameters as $parameter) {
            $name = (string) $parameter->name;
            if (isset($ins[$name])) {
                $paramDoc[$name]['in'] = $ins[$parameter->name];
            }

            if (! isset($paramDoc[$parameter->name])) {
                $paramDoc[$name] = [];
            }

            $paramDoc = $this->paramType($paramDoc, $parameter);
            $paramDoc = $this->paramDefault($paramDoc, $parameter);
        }

        $required = $this->getRequired($parameters);

        return $this->setParamMetas($paramDoc, $required);
    }

    /**
     * @param array<ReflectionParameter> $parameters
     *
     * @return string[]
     * @psalm-return list<string>
     */
    private function getRequired(array $parameters): array
    {
        $required = [];
        foreach ($parameters as $parameter) {
            if ($parameter->isOptional()) {
                continue;
            }

            $required[] = $parameter->name;
        }

        return $required;
    }

    /**
     * @param array<string, array{type?: string, description?: string}> $paramDoc
     *
     * @return array<string, array{type?: string, description?: string, default?: string}>
     *
     * @throws ReflectionException
     */
    private function paramDefault(array $paramDoc, ReflectionParameter $parameter): array
    {
        $hasDefault = $parameter->isDefaultValueAvailable() && $parameter->getDefaultValue() !== null;
        if ($hasDefault) {
            $default = $parameter->getDefaultValue();
            $paramDoc[(string) $parameter->name]['default'] = is_array($default) ? '[]' : (string) $parameter->getDefaultValue(); // @phpstan-ignore-lines
        }

        return $paramDoc;
    }

    /**
     * @param array<string, array{type?: string, description?: string, default?: string, in?: string}> $paramDoc
     *
     * @return array<string, array{type?: string, description?: string, default?: string, in?: string}>
     */
    private function paramType(array $paramDoc, ReflectionParameter $parameter): array
    {
        $type = $this->getParameterType($parameter, $paramDoc, $parameter->name);
        if (is_string($type)) {
            $paramDoc[(string) $parameter->name]['type'] = $type; // override type parameter by reflection over phpdoc param type
        }

        return $paramDoc;
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
     * @param array<string, array{type?: string}> $paramDoc
     * @param list<string>                        $required
     *
     * @return array{parameters?: array<string, array{type?: string}>, required?: array<int, string>}
     */
    private function setParamMetas(array $paramDoc, array $required): array
    {
        $paramMetas = [];
        if ((bool) $paramDoc) {
            $paramMetas['parameters'] = $paramDoc;
        }

        if ((bool) $required) {
            $paramMetas['required'] = $required;
        }

        return $paramMetas;
    }
}
