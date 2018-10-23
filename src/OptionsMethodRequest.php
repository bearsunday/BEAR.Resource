<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\ResourceParam;
use Doctrine\Common\Annotations\Reader;
use Ray\Di\Di\Assisted;

final class OptionsMethodRequest
{
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function __invoke(\ReflectionMethod $method, array $paramDoc, array $ins) : array
    {
        $paramMetas = $this->getParamMetas($method->getParameters(), $paramDoc, $ins);

        return $this->ignoreAnnotatedPrameter($method, $paramMetas);
    }

    /**
     * @return null|string
     */
    private function getParameterType(\ReflectionParameter $parameter, array $paramDoc, string $name)
    {
        $hasType = method_exists($parameter, 'getType') && $parameter->getType();
        if ($hasType) {
            return $this->getType($parameter);
        }
        if (isset($paramDoc[$name]['type'])) {
            return $paramDoc[$name]['type'];
        }
    }

    private function getParamMetas(array $parameters, array $paramDoc, array $ins) : array
    {
        foreach ($parameters as $parameter) {
            if (isset($ins[$parameter->name])) {
                $paramDoc[$parameter->name]['in'] = $ins[$parameter->name];
            }
            if (! isset($paramDoc[$parameter->name])) {
                $paramDoc[$parameter->name] = [];
            }
            $paramDoc = $this->paramType($paramDoc, $parameter);
            $paramDoc = $this->paramDefault($paramDoc, $parameter);
        }
        $required = $this->getRequired($parameters);

        return $this->setParamMetas($paramDoc, $required);
    }

    /**
     * @param \ReflectionParameter[] $parameters
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

    private function paramDefault(array $paramDoc, \ReflectionParameter $parameter) : array
    {
        $hasDefault = $parameter->isDefaultValueAvailable() && $parameter->getDefaultValue() !== null;
        if ($hasDefault) {
            $paramDoc[$parameter->name]['default'] = (string) $parameter->getDefaultValue();
        }

        return $paramDoc;
    }

    private function paramType(array $paramDoc, \ReflectionParameter $parameter) : array
    {
        $type = $this->getParameterType($parameter, $paramDoc, $parameter->name);
        if (is_string($type)) {
            $paramDoc[$parameter->name]['type'] = $type;
        }

        return $paramDoc;
    }

    private function getType(\ReflectionParameter $parameter) : string
    {
        $type = (string) $parameter->getType();
        if ($type === 'int') {
            $type = 'integer';
        }

        return $type;
    }

    /**
     * Ignore @ Assisted @ ResourceParam parameter
     */
    private function ignoreAnnotatedPrameter(\ReflectionMethod $method, array $paramMetas) : array
    {
        $annotations = $this->reader->getMethodAnnotations($method);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof ResourceParam) {
                unset($paramMetas['parameters'][$annotation->param]);
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
     */
    private function ignorreAssisted(array $paramMetas, Assisted $annotation) : array
    {
        $paramMetas['required'] = array_values(array_diff($paramMetas['required'], $annotation->values));
        foreach ($annotation->values as $varName) {
            unset($paramMetas['parameters'][$varName]);
        }

        return $paramMetas;
    }

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
