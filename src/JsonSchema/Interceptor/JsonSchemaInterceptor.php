<?php

declare(strict_types=1);

namespace BEAR\Resource\Interceptor;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\Code;
use BEAR\Resource\Exception\JsonSchemaKeytNotFoundException;
use BEAR\Resource\Exception\JsonSchemaNotFoundException;
use BEAR\Resource\Exception\JsonSchemaRequestException;
use BEAR\Resource\Exception\JsonSchemaResponseException;
use BEAR\Resource\JsonSchema\JsonSchemaErrorMapper;
use BEAR\Resource\JsonSchema\JsonSchemaErrors;
use BEAR\Resource\JsonSchemaExceptionHandlerInterface;
use BEAR\Resource\JsonSchemaRequestExceptionHandlerInterface;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\Types;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Override;
use Ray\Aop\MethodInvocation;
use Ray\Di\Di\Named;
use Ray\InputQuery\Attribute\Input;
use Ray\InputQuery\ToArray;
use ReflectionAttribute;
use ReflectionClass;
use stdClass;

use function assert;
use function file_exists;
use function file_get_contents;
use function is_array;
use function is_dir;
use function is_object;
use function is_string;
use function json_decode;
use function json_encode;
use function property_exists;
use function sprintf;
use function str_replace;

use const JSON_THROW_ON_ERROR;

/**
 * @psalm-import-type Query from Types
 * @psalm-import-type Body from Types
 * @psalm-import-type JsonSchemaValidatorErrors from Types
 */
final readonly class JsonSchemaInterceptor implements JsonSchemaInterceptorInterface
{
    private const HEADER_ETAG = 'ETag';

    /** @psalm-pure */
    public function __construct(
        #[Named('json_schema_dir')]
        private string $schemaDir,
        #[Named('json_validate_dir')]
        private string $validateDir,
        private JsonSchemaExceptionHandlerInterface $handler,
        private JsonSchemaRequestExceptionHandlerInterface $requestHandler,
        #[Named('json_schema_host')]
        private string|null $schemaHost = null,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    #[Override]
    public function invoke(MethodInvocation $invocation): ResourceObject
    {
        $method = $invocation->getMethod();
        $attributes = $method->getAttributes(JsonSchema::class);
        $jsonSchema = isset($attributes[0]) ? $attributes[0]->newInstance() : null;
        assert($jsonSchema instanceof JsonSchema);
        if ($jsonSchema->params) {
            $arguments = $this->getNamedArguments($invocation);
            $this->validateRequest($invocation, $jsonSchema, $arguments);
        }

        $ro = $invocation->proceed();
        assert($ro instanceof ResourceObject);
        if ($ro->code === 200 || $ro->code === 201) {
            $this->validateResponse($ro, $jsonSchema);
        }

        return $ro;
    }

    /**
     * @param Query $arguments
     *
     * MethodInvocation<T> generic cannot be specified without breaking interface compatibility
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     * @phpstan-ignore-next-line missingType.generics
     */
    private function validateRequest(MethodInvocation $invocation, JsonSchema $jsonSchema, array $arguments): void
    {
        try {
            $schemaFile = $this->validateDir . '/' . $jsonSchema->params;
            $this->validateFileExists($schemaFile);
            $this->validateAsRequest($arguments, $schemaFile);
        } catch (JsonSchemaRequestException $e) {
            $ro = $invocation->getThis();
            assert($ro instanceof ResourceObject);
            /** @psalm-suppress PossiblyUndefinedVariable */
            $this->requestHandler->handleRequestException($arguments, $ro, $e, $schemaFile);
        }
    }

    /** @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time */
    private function validateResponse(ResourceObject $ro, JsonSchema $jsonSchema): void
    {
        $schemaFile = $this->getSchemaFile($jsonSchema, $ro);
        try {
            if (! $this->isCachedRenderedBodyResponse($ro, $jsonSchema)) {
                $this->validateRo($ro, $schemaFile, $jsonSchema);
            }

            if (is_string($this->schemaHost)) {
                $ro->headers['Link'] = sprintf('<%s%s>; rel="describedby"', $this->schemaHost, $jsonSchema->schema);
            }
        } catch (JsonSchemaResponseException $e) {
            $this->handler->handle($ro, $e, $schemaFile);
        }
    }

    /** @psalm-impure */
    private function validateRo(ResourceObject $ro, string $schemaFile, JsonSchema $jsonSchema): void
    {
        $viewJson = $jsonSchema->target === 'view' ? (string) $ro : json_encode($ro, JSON_THROW_ON_ERROR);
        /** @var array<stdClass>|false|stdClass $json */
        $json = json_decode($viewJson, null, 512, JSON_THROW_ON_ERROR);
        /** @var array<stdClass>|stdClass $target */
        $target = is_object($json) ? $this->getTarget($json, $jsonSchema) : $json;
        $this->validateAsResponse($target, $schemaFile);
    }

    /** @psalm-capabilities read-props */
    private function isCachedRenderedBodyResponse(ResourceObject $ro, JsonSchema $jsonSchema): bool
    {
        if ($jsonSchema->target !== 'body' || ! isset($ro->headers[self::HEADER_ETAG]) || ! is_string($ro->view)) {
            return false;
        }

        // BEAR.QueryRepository restores cached rendered responses as headers/view only.
        return $ro->body === null;
    }

    /** @psalm-pure */
    private function getTarget(object $json, JsonSchema $jsonSchema): mixed
    {
        if ($jsonSchema->key === '') {
            return $json;
        }

        if (! property_exists($json, $jsonSchema->key)) {
            throw new JsonSchemaKeytNotFoundException($jsonSchema->key);
        }

        return $json->{$jsonSchema->key};
    }

    /**
     * @param array<mixed>|stdClass $target
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    private function validateAsRequest(array|stdClass $target, string $schemaFile): void
    {
        $validator = $this->validate($target, $schemaFile);
        if ($validator->isValid()) {
            return;
        }

        [$msg, $errors] = $this->buildValidationFailure($validator, $schemaFile);

        throw new JsonSchemaRequestException($msg, Code::BAD_REQUEST, $errors);
    }

    /**
     * @param array<mixed>|stdClass $target
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    private function validateAsResponse(array|stdClass $target, string $schemaFile): void
    {
        $validator = $this->validate($target, $schemaFile);
        if ($validator->isValid()) {
            return;
        }

        [$msg, $errors] = $this->buildValidationFailure($validator, $schemaFile);

        throw new JsonSchemaResponseException($msg, Code::ERROR, $errors);
    }

    /**
     * @param array<mixed>|stdClass $target
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    private function validate(array|stdClass $target, string $schemaFile): Validator
    {
        $validator = new Validator();
        $schema = (object) ['$ref' => 'file://' . $schemaFile];
        $scanArray = is_array($target) ? $target : $this->deepArray($target);
        $validator->validate($scanArray, $schema, Constraint::CHECK_MODE_TYPE_CAST);

        return $validator;
    }

    /**
     * @return Body
     *
     * @psalm-impure
     */
    private function deepArray(object $values): array
    {
        $result = [];
        // Iterating over object properties yields mixed key/value types
        /** @psalm-suppress MixedAssignment */
        foreach ($values as $key => $value) { // @phpstan-ignore-line
            /** @psalm-suppress MixedArrayOffset */
            $result[$key] = is_object($value) ? $this->deepArray($value) : $value; // @phpstan-ignore-line
        }

        return $result;
    }

    /**
     * @return array{string, JsonSchemaErrors}
     *
     * @psalm-capabilities read-props|write-props|read-globals|write-globals|io|time
     */
    private function buildValidationFailure(Validator $validator, string $schemaFile): array
    {
        /** @var JsonSchemaValidatorErrors $rawErrors */
        $rawErrors = $validator->getErrors();
        $mapper = new JsonSchemaErrorMapper();
        $schema = $this->schema($schemaFile);
        $msg = '';
        $structured = [];
        foreach ($rawErrors as $rawError) {
            $jsonSchemaError = $mapper->toJsonSchemaError($rawError, $schema);
            $msg .= sprintf('[%s] %s; ', $jsonSchemaError->property, $jsonSchemaError->message);
            $structured[] = $jsonSchemaError;
        }

        $msg .= "by {$schemaFile}";

        return [$msg, new JsonSchemaErrors($structured)];
    }

    /**
     * Load the schema for `errorMessage` lookup. Called only after the file
     * has been verified to exist and after justinrainbow has already parsed
     * it successfully, so the happy path is the only realistic one. Any
     * non-object decode result (boolean schema, unreadable file, malformed
     * JSON) is treated as "no schema-aware messages available" — the
     * mapper just falls back to the validator's raw message.
     *
     * @psalm-capabilities io
     */
    private function schema(string $schemaFile): stdClass|null
    {
        // An invalid / unreadable schema collapses to null here by design —
        // validation itself has already parsed the same file, so this is a
        // graceful fallback for `errorMessage` lookup, not error-swallowing.
        /** @psalm-suppress MixedAssignment json_decode() returns mixed by design; narrowed below. */
        $schema = json_decode((string) file_get_contents($schemaFile));

        return $schema instanceof stdClass ? $schema : null;
    }

    /** @psalm-capabilities read-props|io */
    private function getSchemaFile(JsonSchema $jsonSchema, ResourceObject $ro): string
    {
        if (! $jsonSchema->schema) {
            // for BC only
            new ReflectionClass($ro);
            $roFileName = $this->getParentClassName($ro);
            $bcFile = str_replace('.php', '.json', $roFileName);
            if (file_exists($bcFile)) {
                return $bcFile;
            }
        }

        $schemaFile = $this->schemaDir . '/' . $jsonSchema->schema;
        $this->validateFileExists($schemaFile);

        return $schemaFile;
    }

    /** @psalm-capabilities pure */
    private function getParentClassName(ResourceObject $ro): string
    {
        $parent = (new ReflectionClass($ro))->getParentClass();

        /** @var ReflectionClass<object> $parent ResourceObject instances always have a parent class. */
        return (string) $parent->getFileName();
    }

    /** @psalm-capabilities io */
    private function validateFileExists(string $schemaFile): void
    {
        if (! file_exists($schemaFile) || is_dir($schemaFile)) {
            throw new JsonSchemaNotFoundException($schemaFile);
        }
    }

    /**
     * @param MethodInvocation<object> $invocation
     *
     * @return Query
     *
     * @psalm-capabilities write-props|read-globals|write-globals|io|time
     */
    private function getNamedArguments(MethodInvocation $invocation): array
    {
        $parameters = $invocation->getMethod()->getParameters();
        $values = $invocation->getArguments();
        $toArray = new ToArray();
        /** @var Query $arguments */
        $arguments = [];
        foreach ($parameters as $index => $parameter) {
            /** @psalm-suppress MixedAssignment */
            $value = $values[$index] ?? $parameter->getDefaultValue();
            $inputAttributes = $parameter->getAttributes(Input::class, ReflectionAttribute::IS_INSTANCEOF);
            if ($inputAttributes !== [] && is_object($value)) {
                /** @psalm-suppress MixedAssignment */
                foreach ($toArray($value) as $name => $inputValue) {
                    $arguments[$name] = $inputValue;
                }

                continue;
            }

            /** @psalm-suppress MixedAssignment */
            $arguments[$parameter->name] = $value;
        }

        return $arguments;
    }
}
