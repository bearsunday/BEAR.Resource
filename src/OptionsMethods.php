<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\Annotation\ResourceParam;
use Doctrine\Common\Annotations\Reader;
use Ray\Di\Di\Assisted;
use Ray\Di\Di\Named;
use Ray\WebContextParam\Annotation\AbstractWebContextParam;
use Ray\WebContextParam\Annotation\CookieParam;
use Ray\WebContextParam\Annotation\EnvParam;
use Ray\WebContextParam\Annotation\FormParam;
use Ray\WebContextParam\Annotation\QueryParam;
use Ray\WebContextParam\Annotation\ServerParam;

final class OptionsMethods
{
    /**
     * Constants for annotation name and "in" name
     *
     * @var array
     */
    private $webContextName = [
        CookieParam::class => 'cookie',
        EnvParam::class => 'env',
        FormParam::class => 'formData',
        QueryParam::class => 'query',
        ServerParam::class => 'server'
    ];
    private $reader;

    /**
     * @var string
     */
    private $schemaDir;

    /**
     * @Named("schemaDir=json_schema_dir")
     */
    public function __construct(Reader $reader, $schemaDir = '')
    {
        $this->reader = $reader;
        $this->schemaDir = $schemaDir;
    }

    public function __invoke(ResourceObject $ro, string $requestMethod) : array
    {
        $method = new \ReflectionMethod($ro, 'on' . $requestMethod);
        $ins = $this->getInMap($method);
        list($doc, $paramDoc) = (new OptionsMethodsDocBolck)($method);
        $parameters = $method->getParameters();
        $paramMetas = $this->getParamMetas($parameters, $paramDoc, $ins);
        $paramMetas = $this->ignoreAnnotatedPrameter($method, $paramMetas);
        $schema = $this->getJsonSchema($method);
        $request = $paramMetas ? ['request' => $paramMetas] : [];
        if (! empty($schema)) {
            return $doc + $request + ['schema' => $schema];
        }

        return $doc + $request;
    }

    private function getInMap(\ReflectionMethod $method) : array
    {
        $ins = [];
        $annotations = $this->reader->getMethodAnnotations($method);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof AbstractWebContextParam) {
                $ins[$annotation->param] = $this->webContextName[get_class($annotation)];
            }
        }

        return $ins;
    }

    /**
     * @param \ReflectionParameter $parameter
     * @param array                $paramDoc
     * @param string               $name
     *
     * @return string|null
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
        $paramMetas = $this->setParamMetas($paramDoc, $required);

        return $paramMetas;
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

    /**
     * @param \ReflectionParameter $parameter
     */
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

    private function getJsonSchema(\ReflectionMethod $method) : array
    {
        $schema = $this->reader->getMethodAnnotation($method, JsonSchema::class);
        if (! $schema instanceof JsonSchema) {
            return [];
        }
        $schemaFile = $this->schemaDir . '/' . $schema->schema;
        if (! file_exists($schemaFile)) {
            return [];
        }

        return (array) json_decode(file_get_contents($schemaFile));
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
