<?php

declare(strict_types=1);

namespace BEAR\Resource\JsonSchema;

use Countable;
use Generator;
use IteratorAggregate;

use function count;

/**
 * Collection of structured JSON Schema validation errors.
 *
 * Immutable container exposed by JsonSchemaException::getErrors(). The
 * underlying list is private — access it via iteration (`foreach`,
 * `iterator_to_array`), `first()`, or one of the named accessors.
 *
 * @psalm-immutable
 * @implements IteratorAggregate<int, JsonSchemaError>
 */
final readonly class JsonSchemaErrors implements Countable, IteratorAggregate
{
    /**
     * @param list<JsonSchemaError> $errors
     *
     * @psalm-pure
     */
    public function __construct(
        private array $errors,
    ) {
    }

    /** @psalm-capabilities read-props */
    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    /** @psalm-capabilities read-props */
    public function count(): int
    {
        return count($this->errors);
    }

    /**
     * @return Generator<int, JsonSchemaError>
     *
     * @psalm-capabilities pure
     */
    public function getIterator(): Generator
    {
        return (function (): Generator {
            yield from $this->errors;
        })();
    }

    /**
     * First error if any, null on an empty collection.
     *
     * @psalm-capabilities read-props
     */
    public function first(): JsonSchemaError|null
    {
        return $this->errors[0] ?? null;
    }

    /**
     * Group errors by their property path — the core use case for handlers
     * that build a `{field: [messages]}` body for a 422 response or
     * re-render a form with per-field error annotations.
     *
     * @return array<string, list<JsonSchemaError>>
     *
     * @psalm-capabilities read-props
     */
    public function byProperty(): array
    {
        $grouped = [];
        foreach ($this->errors as $error) {
            $grouped[$error->property][] = $error;
        }

        return $grouped;
    }

    /**
     * Render every error through the given template and concatenate the
     * results into one string.
     *
     * The template runs per error through `JsonSchemaError::render()`, so any
     * `{key}` placeholder available there (property, pointer, message, plus
     * `$constraint->params` keys) can appear in it. The template should
     * include its own separator — e.g. `"{message}\n"` for one error per
     * line, `"<li>{message}</li>"` for an HTML list.
     *
     * Returns an empty string when there are no errors.
     *
     * @psalm-capabilities read-props
     */
    public function format(string $template = "{message}\n"): string
    {
        $out = '';
        foreach ($this->errors as $error) {
            $out .= $error->render($template);
        }

        return $out;
    }
}
