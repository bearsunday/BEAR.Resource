<?php

declare(strict_types=1);

namespace BEAR\Resource\Interceptor;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\Code;
use BEAR\Resource\Exception\JsonSchemaException;
use BEAR\Resource\Exception\JsonSchemaNotFoundException;
use BEAR\Resource\JsonSchemaExceptionHandlerInterface;
use BEAR\Resource\ResourceObject;
use function is_string;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Aop\ReflectionMethod;
use Ray\Aop\WeavedInterface;
use Ray\Di\Di\Named;

final class JsonSchemaInterceptor implements MethodInterceptor
{
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
     * @var JsonSchemaExceptionHandlerInterface
     */
    private $handler;

    /**
     * @Named("schemaDir=json_schema_dir,validateDir=json_validate_dir,schemaHost=json_schema_host")
     */
    public function __construct(string $schemaDir, string $validateDir, JsonSchemaExceptionHandlerInterface $handler, string $schemaHost = null)
    {
        $this->schemaDir = $schemaDir;
        $this->validateDir = $validateDir;
        $this->schemaHost = $schemaHost;
        $this->handler = $handler;
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
        if ($ro->code === 200 || $ro->code == 201) {
            $this->validateResponse($ro, $jsonSchema);
        }

        return $ro;
    }

    private function validateRequest(JsonSchema $jsonSchema, array $arguments)
    {
        $schemaFile = $this->validateDir . '/' . $jsonSchema->params;
        $this->validateFileExists($schemaFile);
        $this->validate($arguments, $schemaFile);
    }

    private function validateResponse(ResourceObject $ro, JsonSchema $jsonSchema)
    {
        $schemaFile = $this->getSchemaFile($jsonSchema, $ro);
        try {
            $this->validateRo($ro, $schemaFile);
            if (is_string($this->schemaHost)) {
                $ro->headers['Link'] = sprintf('<%s%s>; rel="describedby"', $this->schemaHost, $jsonSchema->schema);
            }
        } catch (JsonSchemaException $e) {
            $this->handler->handle($ro, $e, $schemaFile);
        }
    }

    private function validateRo(ResourceObject $ro, string $schemaFile)
    {
        $validator = new Validator;
        $json = json_decode((string) $ro);
        $this->validate($json, $schemaFile);
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

        throw $this->throwJsonSchemaException($validator);
    }

    private function throwJsonSchemaException(Validator $validator) : JsonSchemaException
    {
        $errors = $validator->getErrors();
        $msg = '';
        foreach ($errors as $error) {
            $msg .= sprintf('[%s] %s; ', $error['property'], $error['message']);
        }

        return new JsonSchemaException($msg, Code::ERROR);
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
