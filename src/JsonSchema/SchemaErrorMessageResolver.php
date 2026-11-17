<?php

declare(strict_types=1);

namespace BEAR\Resource\JsonSchema;

use stdClass;

use function ctype_digit;
use function explode;
use function is_array;
use function is_string;
use function ltrim;
use function property_exists;
use function str_replace;

/**
 * Resolve the schema-declared `errorMessage` (ajv-errors style) for a
 * validator failure, navigating the schema by JSON Pointer so nested
 * objects/arrays resolve correctly.
 *
 * Known limitations (documented; deferred to future PRs):
 *  - `$ref` is not followed when navigating the schema tree.
 *  - `allOf` / `oneOf` / `anyOf` combinators are not merged.
 *  - `patternProperties` cannot be reached by Pointer alone.
 *  - `additionalProperties` schemas (keys not declared under `properties`)
 *    are not resolved — Pointer ends at a node the walker can't enter.
 *
 * @internal
 * @psalm-immutable
 */
final class SchemaErrorMessageResolver
{
    /**
     * Returns the raw template string declared in the schema, or null when
     * the schema does not override this constraint's message.
     *
     * @psalm-capabilities read-props
     */
    public function resolve(stdClass|null $schema, JsonSchemaError $error): string|null
    {
        if ($schema === null) {
            return null;
        }

        $node = $this->navigateByPointer($schema, $error->pointer);
        if ($node === null) {
            return null;
        }

        if ($error->constraint->name === 'required') {
            return $this->requiredMessage($node, $error->property);
        }

        return $this->constraintMessage($node, $error->constraint->name);
    }

    /** @psalm-capabilities read-props */
    private function requiredMessage(stdClass $schema, string $property): string|null
    {
        if (! property_exists($schema, 'errorMessage') || ! $schema->errorMessage instanceof stdClass) {
            return null;
        }

        $errorMessage = $schema->errorMessage;
        if (! property_exists($errorMessage, 'required')) {
            return null;
        }

        /** @psalm-suppress MixedAssignment Schema extension values are narrowed below. */
        $required = $errorMessage->required;
        if (is_string($required)) {
            return $required;
        }

        if (! $required instanceof stdClass || ! property_exists($required, $property)) {
            return null;
        }

        /** @psalm-suppress MixedAssignment Schema extension values are narrowed below. */
        $message = $required->{$property};

        return is_string($message) ? $message : null;
    }

    /** @psalm-pure */
    private function constraintMessage(stdClass $propertyNode, string $constraint): string|null
    {
        if (! property_exists($propertyNode, 'errorMessage')) {
            return null;
        }

        /** @psalm-suppress MixedAssignment Schema extension values are narrowed below. */
        $errorMessage = $propertyNode->errorMessage;
        if (is_string($errorMessage)) {
            return $errorMessage;
        }

        if (! $errorMessage instanceof stdClass || ! property_exists($errorMessage, $constraint)) {
            return null;
        }

        /** @psalm-suppress MixedAssignment Schema extension values are narrowed below. */
        $message = $errorMessage->{$constraint};

        return is_string($message) ? $message : null;
    }

    /**
     * Walk a JSON Pointer through the schema, descending via `properties`
     * for string segments and via `items` for numeric (array-index) segments.
     *
     * @psalm-capabilities read-props
     */
    private function navigateByPointer(stdClass $schema, string $pointer): stdClass|null
    {
        if ($pointer === '' || $pointer === '/') {
            return $schema;
        }

        $segments = explode('/', ltrim($pointer, '/'));
        $node = $schema;
        foreach ($segments as $rawSegment) {
            // RFC 6901 escaping: ~1 -> /, ~0 -> ~ (decode ~1 first to avoid double-translating).
            $segment = str_replace(['~1', '~0'], ['/', '~'], $rawSegment);
            $next = ctype_digit($segment) ? $this->descendArray($node, $segment) : $this->descendObject($node, $segment);
            if ($next === null) {
                return null;
            }

            $node = $next;
        }

        return $node;
    }

    /**
     * Numeric pointer segment: descend through the parent's `items` schema (single or tuple).
     *
     * @psalm-pure
     */
    private function descendArray(stdClass $node, string $index): stdClass|null
    {
        if (! property_exists($node, 'items')) {
            return null;
        }

        /** @psalm-suppress MixedAssignment Schema values are narrowed below. */
        $items = $node->items;
        if ($items instanceof stdClass) {
            return $items;
        }

        if (is_array($items)) {
            /** @psalm-suppress MixedAssignment Tuple-form `items` carries mixed entries. */
            $tupleNode = $items[(int) $index] ?? null;

            return $tupleNode instanceof stdClass ? $tupleNode : null;
        }

        return null;
    }

    /**
     * String pointer segment: descend through the parent's `properties.<segment>` schema.
     *
     * @psalm-pure
     */
    private function descendObject(stdClass $node, string $segment): stdClass|null
    {
        if (! property_exists($node, 'properties')) {
            return null;
        }

        /** @psalm-suppress MixedAssignment Schema values are narrowed below. */
        $properties = $node->properties;
        if (! $properties instanceof stdClass || ! property_exists($properties, $segment)) {
            return null;
        }

        /** @psalm-suppress MixedAssignment Schema values are narrowed below. */
        $next = $properties->{$segment};

        return $next instanceof stdClass ? $next : null;
    }
}
