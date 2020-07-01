<?php

declare(strict_types=1);

namespace BEAR\Resource\Interceptor;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\Code;
use BEAR\Resource\Exception\JsonSchemaException;
use BEAR\Resource\Exception\JsonSchemaKeytFoundException;
use BEAR\Resource\Exception\JsonSchemaNotFoundException;
use BEAR\Resource\JsonSchemaExceptionHandlerInterface;
use BEAR\Resource\ResourceObject;
use function is_array;
use function is_object;
use function is_string;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Di\Di\Named;
use ReflectionClass;
use stdClass;

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
     * @var ?string
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

    /**
     * @param array<string, mixed> $arguments
     */
    private function validateRequest(JsonSchema $jsonSchema, array $arguments) : void
    {
        $schemaFile = $this->validateDir . '/' . $jsonSchema->params;
        $this->validateFileExists($schemaFile);
        $this->validate($arguments, $schemaFile);
    }

    private function validateResponse(ResourceObject $ro, JsonSchema $jsonSchema) : void
    {
        $schemaFile = $this->getSchemaFile($jsonSchema, $ro);
        try {
            $this->validateRo($ro, $schemaFile, $jsonSchema);
            if (is_string($this->schemaHost)) {
                $ro->headers['Link'] = sprintf('<%s%s>; rel="describedby"', $this->schemaHost, $jsonSchema->schema);
            }
        } catch (JsonSchemaException $e) {
            $this->handler->handle($ro, $e, $schemaFile);
        }
    }

    private function validateRo(ResourceObject $ro, string $schemaFile, JsonSchema $jsonSchema) : void
    {
        /** @var array<stdClass>|false|stdClass $json */
        $json = json_decode((string) $ro);
        if (! $json) {
            return;
        }
        /** @var array<stdClass>|stdClass $target */
        $target = is_object($json) ? $this->getTarget($json, $jsonSchema) : $json;
        $this->validate($target, $schemaFile);
    }

    /**
     * @return mixed
     */
    private function getTarget(object $json, JsonSchema $jsonSchema)
    {
        if ($jsonSchema->key === '') {
            return $json;
        }
        if (! $json->{$jsonSchema->key}) {
            throw new JsonSchemaKeytFoundException($jsonSchema->key);
        }

        return $json->{$jsonSchema->key};
    }

    /**
     * @param array<stdClass>|array<string, mixed>|stdClass $target
     */
    private function validate($target, string $schemaFile) : void
    {
        $validator = new Validator;
        $schema = (object) ['$ref' => 'file://' . $schemaFile];
        $scanArray = is_array($target) ? $target : $this->deepArray($target);
        $validator->validate($scanArray, $schema, Constraint::CHECK_MODE_TYPE_CAST);
        $isValid = (bool) $validator->isValid();
        if ($isValid) {
            return;
        }

        throw $this->throwJsonSchemaException($validator, $schemaFile);
    }

    /**
     * @return array<int|string, mixed>
     */
    private function deepArray(object $values) : array
    {
        $result = [];
        /** @psalm-suppress MixedAssignment */
        foreach ($values as $key => $value) { // @phpstan-ignore-line
            /** @psalm-suppress MixedArrayOffset */
            $result[$key] = is_object($value) ? $this->deepArray($value) : $result[$key] = $value;
        }

        return $result;
    }

    private function throwJsonSchemaException(Validator $validator, string $schemaFile) : JsonSchemaException
    {
        /** @var array<array<string, string>> $errors */
        $errors = $validator->getErrors();
        $msg = '';
        foreach ($errors as $error) {
            $msg .= sprintf('[%s] %s; ', $error['property'], $error['message']);
        }
        $msg .= "by {$schemaFile}";

        return new JsonSchemaException($msg, Code::ERROR);
    }

    private function getSchemaFile(JsonSchema $jsonSchema, ResourceObject $ro) : string
    {
        if (! $jsonSchema->schema) {
            // for BC only
            new ReflectionClass($ro);
            $roFileName = $this->getParentClassName($ro);
            $bcFile = str_replace('.php', '.json', (string) $roFileName);
            if (file_exists($bcFile)) {
                return $bcFile;
            }
        }
        $schemaFile = $this->schemaDir . '/' . $jsonSchema->schema;
        $this->validateFileExists($schemaFile);

        return $schemaFile;
    }

    private function getParentClassName(ResourceObject $ro) : string
    {
        $parent = (new ReflectionClass($ro))->getParentClass();

        return  $parent instanceof ReflectionClass ? (string) $parent->getFileName() : '';
    }

    private function validateFileExists(string $schemaFile) : void
    {
        if (! file_exists($schemaFile) || is_dir($schemaFile)) {
            throw new JsonSchemaNotFoundException($schemaFile);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function getNamedArguments(MethodInvocation $invocation)
    {
        $parameters = $invocation->getMethod()->getParameters();
        $values = $invocation->getArguments();
        $arguments = [];
        foreach ($parameters as $index => $parameter) {
            /** @psalm-suppress MixedAssignment */
            $arguments[(string) $parameter->name] = $values[$index] ?? $parameter->getDefaultValue();
        }

        return $arguments;
    }
}
