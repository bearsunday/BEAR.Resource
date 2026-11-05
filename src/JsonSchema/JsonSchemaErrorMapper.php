<?php

declare(strict_types=1);

namespace BEAR\Resource\JsonSchema;

use BEAR\Resource\Types;
use stdClass;

use function in_array;
use function is_array;
use function is_string;

/**
 * @internal
 * @psalm-import-type JsonSchemaValidatorError from Types
 * @psalm-immutable
 */
final class JsonSchemaErrorMapper
{
    /** Standard row keys consumed by JsonSchemaError; everything else on a 5.x row is treated as a flattened constraint param. */
    private const ROW_KEYS = ['property', 'pointer', 'message', 'constraint', 'context'];

    private SchemaErrorMessageResolver $resolver;

    /** @psalm-capabilities read-props */
    public function __construct(SchemaErrorMessageResolver|null $resolver = null)
    {
        $this->resolver = $resolver ?? new SchemaErrorMessageResolver();
    }

    /**
     * Normalize one justinrainbow validator error row into a typed JsonSchemaError.
     *
     * @param JsonSchemaValidatorError $error
     *
     * @psalm-suppress MixedAssignment Upstream validator returns mixed-typed row values; each field is narrowed below.
     *
     * @psalm-capabilities read-props
     */
    public function toJsonSchemaError(array $error, stdClass|null $schema = null): JsonSchemaError
    {
        $propertyRaw = $error['property'] ?? null;
        $pointerRaw = $error['pointer'] ?? null;
        $messageRaw = $error['message'] ?? null;
        $constraint = $this->toConstraintViolation($error);

        $jsonSchemaError = new JsonSchemaError(
            $this->property(is_string($propertyRaw) ? $propertyRaw : '', $constraint),
            is_string($pointerRaw) ? $pointerRaw : '',
            is_string($messageRaw) ? $messageRaw : '',
            $constraint,
        );
        $template = $this->resolver->resolve($schema, $jsonSchemaError);

        return $template === null ? $jsonSchemaError : new JsonSchemaError(
            $jsonSchemaError->property,
            $jsonSchemaError->pointer,
            $jsonSchemaError->render($template),
            $jsonSchemaError->constraint,
            $jsonSchemaError->rawMessage,
        );
    }

    /**
     * Accepts both upstream shapes:
     * - 5.x: `constraint` is a string keyword; params are flattened onto the parent row (`minimum`, `maxLength`, etc.)
     * - 6.x: `constraint` is `array{name: string, params: array<string, mixed>}`
     *
     * @param JsonSchemaValidatorError $error
     *
     * @psalm-suppress MixedAssignment Upstream constraint is mixed; each field is narrowed below.
     *
     * @psalm-capabilities read-props
     */
    private function toConstraintViolation(array $error): ConstraintViolation
    {
        $constraint = $error['constraint'] ?? null;

        if (is_string($constraint)) {
            return new ConstraintViolation($constraint, $this->flattenedParams($error));
        }

        if (! is_array($constraint)) {
            return new ConstraintViolation('unknown', []);
        }

        $nameRaw = $constraint['name'] ?? null;
        $paramsRaw = $constraint['params'] ?? null;
        /** @var array<string, mixed> $params */
        $params = is_array($paramsRaw) ? $paramsRaw : [];

        return new ConstraintViolation(
            is_string($nameRaw) ? $nameRaw : 'unknown',
            $params,
        );
    }

    /**
     * Collect the 5.x “flattened” params: everything on the row that isn't a standard JsonSchemaError field.
     *
     * @param JsonSchemaValidatorError $error
     *
     * @return array<string, mixed>
     *
     * @psalm-suppress MixedAssignment Upstream row values are mixed; we pass them through as-is.
     *
     * @psalm-pure
     */
    private function flattenedParams(array $error): array
    {
        $params = [];
        foreach ($error as $key => $value) {
            if (! is_string($key) || in_array($key, self::ROW_KEYS, true)) {
                continue;
            }

            $params[$key] = $value;
        }

        return $params;
    }

    /** @psalm-pure */
    private function property(string $property, ConstraintViolation $constraint): string
    {
        if ($constraint->name !== 'required') {
            return $property;
        }

        /** @psalm-suppress MixedAssignment Constraint params intentionally carry mixed upstream values. */
        $missing = $constraint->params['property'] ?? null;

        return is_string($missing) && $missing !== '' ? $missing : $property;
    }
}
