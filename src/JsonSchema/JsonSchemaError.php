<?php

declare(strict_types=1);

namespace BEAR\Resource\JsonSchema;

use function array_merge;
use function get_debug_type;
use function is_array;
use function is_bool;
use function is_numeric;
use function is_string;
use function json_encode;
use function str_replace;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_UNICODE;

/** @psalm-immutable */
final readonly class JsonSchemaError
{
    public string $rawMessage;

    /**
     * True when `$message` differs from `$rawMessage` — typically because the
     * mapper rendered a schema-side `errorMessage` (ajv-errors style) on top
     * of the validator's original. False when the two are identical, i.e. no
     * effective customization was applied.
     */
    public bool $isCustomMessage;

    /** @psalm-pure */
    public function __construct(
        public string $property,
        public string $pointer,
        public string $message,
        public ConstraintViolation $constraint,
        string $rawMessage = '',
    ) {
        $this->rawMessage = $rawMessage === '' ? $message : $rawMessage;
        $this->isCustomMessage = $rawMessage !== '' && $rawMessage !== $message;
    }

    /**
     * Render a `{key}` placeholder template using this error's data.
     *
     * Use this when the schema (e.g. ajv-errors `errorMessage`) provides
     * a custom message template that should be filled with the violated
     * constraint's params. Available placeholder keys:
     *  - `{property}`, `{pointer}`, `{message}`
     *  - every key in `$this->constraint->params`
     *
     * Unknown placeholders are left in place. Mirrors be-framework's
     * `#[Message]` `{key}` syntax and value-stringification rules.
     */
    public function render(string $template): string
    {
        // Reserved keys win over constraint params if upstream collides on a name like 'property'.
        $vars = array_merge($this->constraint->params, [
            'property' => $this->property,
            'pointer' => $this->pointer,
            'message' => $this->message,
        ]);

        /** @psalm-suppress MixedAssignment */
        foreach ($vars as $key => $value) {
            $template = str_replace('{' . $key . '}', $this->stringify($value), $template);
        }

        return $template;
    }

    /**
     * Mirrors Be\Framework\SemanticVariable\ValidationMessageHandler::interpolateTemplate().
     */
    private function stringify(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        }

        return get_debug_type($value);
    }
}
