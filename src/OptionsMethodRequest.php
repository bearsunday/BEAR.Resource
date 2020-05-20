<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\ResourceParam;
use Doctrine\Common\Annotations\Reader;
use Ray\Di\Di\Assisted;
use ReflectionParameter;

final class OptionsMethodRequest
{
    /**
     * @var Reader
     */
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Parameter #2 $paramMetas of method BEAR\Resource\OptionsMethodRequest::ignoreAnnotatedPrameter() expects array('parameters' => array<string, array('type' =>
     *
     * @param array<string, array{type: string, description?: string}> $paramDoc
     * @param array<string, string>                                    $ins
     *
     * @return array{parameters?: array<string, array{type?: string, description?: string, default?: string}>, required?: array<int, string>}
     */
    public function __invoke(\ReflectionMethod $method, array $paramDoc, array $ins) : array
    {
        $paramMetas = $this->getParamMetas($method->getParameters(), $paramDoc, $ins);

        return $this->ignoreAnnotatedPrameter($method, $paramMetas);
    }

    /**
     * @param array<string, array{type?: string, description?: string}> $paramDoc
     *
     * @return ?string
     *
     * @psalm-suppress RedundantCondition for BC
     */
    private function getParameterType(ReflectionParameter $parameter, array $paramDoc, string $name)
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
    private function getParamMetas(array $parameters, array $paramDoc, array $ins) : array
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
     *
     * @psalm-return list<string>
     */
    private function getRequired(array $parameters) : array
    {
        $required = [];
        foreach ($parameters as $parameter) {
            if (! $parameter->isOptional()) {
                $required[] = $parameter->name;
            }
        }

        return $required;
    }

    /**
     * @param array<string, array{type?: string, description?: string}> $paramDoc
     *
     * @throws \ReflectionException
     *
     * @return array<string, array{type?: string, description?: string, default?: string}>
     */
    private function paramDefault(array $paramDoc, ReflectionParameter $parameter) : array
    {
        $hasDefault = $parameter->isDefaultValueAvailable() && $parameter->getDefaultValue() !== null;
        if ($hasDefault) {
            $default = $parameter->getDefaultValue();
            $paramDoc[(string) $parameter->name]['default'] = is_array($default) ? '[]' : (string) $parameter->getDefaultValue();
        }

        return $paramDoc;
    }

    /**
     * @param array<string, array{type?: string, description?: string, default?: string, in?: string}> $paramDoc
     *
     * @return array<string, array{type?: string, description?: string, default?: string, in?: string}>
     */
    private function paramType(array $paramDoc, ReflectionParameter $parameter) : array
    {
        $type = $this->getParameterType($parameter, $paramDoc, $parameter->name);
        if (is_string($type)) {
            $paramDoc[(string) $parameter->name]['type'] = $type; // override type parameter by reflection over phpdoc param type
        }

        return $paramDoc;
    }

    private function getType(ReflectionParameter $parameter) : string
    {
        $namedType = $parameter->getType();
        assert($namedType instanceof \ReflectionNamedType);
        $type = $namedType->getName();
        if ($type === 'int') {
            $type = 'integer';
        }

        return $type;
    }

    /**
     * Ignore @ Assisted @ ResourceParam parameter
     *
     * @param array{parameters?: array<string, array{type?: string}>, required?: array<int, string>} $paramMetas
     *
     * @return array{parameters?: array<string, array{type?: string, description?: string}>, required?: array<int, string>}
     */
    private function ignoreAnnotatedPrameter(\ReflectionMethod $method, array $paramMetas) : array
    {
        $annotations = $this->reader->getMethodAnnotations($method);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof ResourceParam) {
                /* @psalm-suppress UndefinedClass */
                unset($paramMetas['parameters'][$annotation->param]); // @phpstan-ignore-line
                assert(isset($paramMetas['required']));
                $paramMetas['required'] = array_values(array_diff($paramMetas['required'], [$annotation->param]));
            }
            if ($annotation instanceof Assisted) {
                $paramMetas = $this->ignorreAssisted($paramMetas, $annotation);
            }
        }

        return $paramMetas;
    }

    /**
     * Ignore @ Assisted parameter
     *
     * @param array{parameters?: array<string, array{type?: string, description?: string}>, required?: array<int, string>} $paramMetas
     *
     * @return (string|string[])[][]
     *
     * @psalm-return array{parameters?: array<string, array{type?: string, description?: string}>, required?: array<int, string>}
     */
    private function ignorreAssisted(array $paramMetas, Assisted $annotation) : array
    {
        if (isset($paramMetas['required'])) {
            $paramMetas['required'] = array_values(array_diff($paramMetas['required'], $annotation->values));
        }
        foreach ($annotation->values as $varName) {
            unset($paramMetas['parameters'][$varName]); // @phpstan-ignore-lineJsonSchemaInterceptor.php
        }

        return $paramMetas;
    }

    /**
     * @param array<string, array{type?: string}> $paramDoc
     * @param array<int, string>                  $required
     *
     * @return array{parameters?: array<string, array{type?: string}>, required?: array<int, string>}
     */
    private function setParamMetas(array $paramDoc, array $required) : array
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
