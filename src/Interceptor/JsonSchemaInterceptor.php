<?php

declare(strict_types=1);

namespace BEAR\Resource\Interceptor;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\Code;
use BEAR\Resource\Exception\JsonSchemaErrorException;
use BEAR\Resource\Exception\JsonSchemaException;
use BEAR\Resource\Exception\JsonSchemaNotFoundException;
use BEAR\Resource\ResourceObject;
use function file_get_contents;
use function is_string;
use function json_decode;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use JSONSchemaFaker\Faker;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Aop\ReflectionMethod;
use Ray\Aop\WeavedInterface;
use Ray\Di\Di\Named;

final class JsonSchemaInterceptor implements MethodInterceptor
{
    const X_FAKE_JSON = 'X-Fake-JSON';

    /**
     * @var string
     */
    private $schemaDir;

    /**
     * @var string
     */
    private $validateDir;

    /**
     * @var null|string
     */
    private $schemaHost;

    /**
     * @var bool
     */
    private $enableFakeJson;

    /**
     * @Named("schemaDir=json_schema_dir,validateDir=json_validate_dir,schemaHost=json_schema_host,enableFakeJson=enable_fake_json")
     */
    public function __construct(string $schemaDir, string $validateDir, string $schemaHost = null, bool $enableFakeJson = false)
    {
        $this->schemaDir = $schemaDir;
        $this->validateDir = $validateDir;
        $this->schemaHost = $schemaHost;
        $this->enableFakeJson = $enableFakeJson;
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        /** @var ReflectionMethod $method */
        $method = $invocation->getMethod();
        /** @var JsonSchema $jsonSchema */
        $jsonSchema = $method->getAnnotation(JsonSchema::class);
        if ($jsonSchema->params) {
            $arguments = $this->getNamedArguments($invocation);
            $this->validateRequest($jsonSchema, $arguments);
        }
        /** @var ResourceObject $ro */
        $ro = $invocation->proceed();
        if (is_string($this->schemaHost)) {
            $ro->headers['Link'] = sprintf('<%s%s>; rel="describedby"', $this->schemaHost, $jsonSchema->schema);
        }
        if ($ro->code !== 200 && $ro->code !== 201) {
            return $ro;
        }
        try {
            $this->validateResponse($jsonSchema, $ro);
        } catch (JsonSchemaException $e) {
            if ($this->enableFakeJson) {
                $ro->headers[self::X_FAKE_JSON] = $jsonSchema->schema;
                $ro->body = $this->fakeResponse($jsonSchema);

                return $ro;
            }

            throw $e;
        }

        return $ro;
    }

    private function fakeResponse(JsonSchema $jsonSchema) : array
    {
        $schemaFile = $this->schemaDir . '/' . $jsonSchema->schema;
        $this->validateFileExists($schemaFile);
        $schema = json_decode(file_get_contents($schemaFile));
        $fakeJson = (new Faker($this->schemaDir))->generate($schema);

        return $this->deepArray($fakeJson);
    }

    private function deepArray($values) : array
    {
        $result = [];
        foreach ($values as $key => $value) {
            $result[$key] = is_object($value) ? $this->deepArray((array) $value) : $result[$key] = $value;
        }

        return $result;
    }

    private function validateRequest(JsonSchema $jsonSchema, array $arguments)
    {
        $schemaFile = $this->validateDir . '/' . $jsonSchema->params;
        $this->validateFileExists($schemaFile);
        $this->validate($arguments, $schemaFile);
    }

    private function validateResponse(JsonSchema $jsonSchema, ResourceObject $ro)
    {
        $schemaFile = $this->getSchemaFile($jsonSchema, $ro);
        $body = isset($ro->body[$jsonSchema->key]) ? $ro->body[$jsonSchema->key] : $ro->body;
        $this->validateRo($ro, $schemaFile);
    }

    private function validateRo(ResourceObject $ro, string $schemaFile)
    {
        $validator = new Validator;
        $schema = (object) ['$ref' => 'file://' . $schemaFile];
        $view = (string) $ro;
        $data = json_decode($view);
        $validator->validate($data, $schema, Constraint::CHECK_MODE_TYPE_CAST);
        $isValid = $validator->isValid();
        if ($isValid) {
            return;
        }
        $e = null;
        $msgList = '';
        foreach ($validator->getErrors() as $error) {
            $msg = sprintf('[%s] %s', $error['property'], $error['message']);
            $msgList .= $msg . '; ';
            $e = $e ? new JsonSchemaErrorException($msg, 0, $e) : new JsonSchemaErrorException($msg);
        }

        throw new JsonSchemaException("{$msgList} in {$schemaFile}", Code::ERROR, $e);
    }

    private function validate($scanObject, $schemaFile)
    {
        $validator = new Validator;
        $schema = (object) ['$ref' => 'file://' . $schemaFile];

        $validator->validate($scanObject, $schema, Constraint::CHECK_MODE_TYPE_CAST);
        $isValid = $validator->isValid();
        if ($isValid) {
            return;
        }
        $e = null;
        foreach ($validator->getErrors() as $error) {
            $msg = sprintf('[%s] %s', $error['property'], $error['message']);
            $e = $e ? new JsonSchemaErrorException($msg, 0, $e) : new JsonSchemaErrorException($msg);
        }

        throw new JsonSchemaException($schemaFile, Code::ERROR, $e);
    }

    private function getSchemaFile(JsonSchema $jsonSchema, ResourceObject $ro) : string
    {
        if (! $jsonSchema->schema) {
            // for BC only
            $ref = new \ReflectionClass($ro);
            if (! $ref instanceof \ReflectionClass) {
                throw new \ReflectionException(get_class($ro)); // @codeCoverageIgnore
            }
            $roFileName = $ro instanceof WeavedInterface ? $roFileName = $ref->getParentClass()->getFileName() : $ref->getFileName();
            $bcFile = str_replace('.php', '.json', $roFileName);
            if (file_exists($bcFile)) {
                return $bcFile;
            }
        }
        $schemaFile = $this->schemaDir . '/' . $jsonSchema->schema;
        $this->validateFileExists($schemaFile);

        return $schemaFile;
    }

    private function validateFileExists(string $schemaFile)
    {
        if (! file_exists($schemaFile) || is_dir($schemaFile)) {
            throw new JsonSchemaNotFoundException($schemaFile);
        }
    }

    private function getNamedArguments(MethodInvocation $invocation)
    {
        $parameters = $invocation->getMethod()->getParameters();
        $values = $invocation->getArguments();
        $arguments = [];
        foreach ($parameters as $index => $parameter) {
            $arguments[$parameter->name] = $values[$index] ?? $parameter->getDefaultValue();
        }

        return $arguments;
    }
}
