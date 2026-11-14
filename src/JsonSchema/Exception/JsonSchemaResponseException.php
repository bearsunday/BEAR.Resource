<?php

declare(strict_types=1);

namespace BEAR\Resource\Exception;

use BEAR\Resource\Code;
use BEAR\Resource\JsonSchema\JsonSchemaErrors;
use BEAR\Resource\Types;

/**
 * Thrown when a JSON Schema validation against the resource's response body fails.
 *
 * Carries a 5xx-class `Code::*` value — the precise code (500, 503, …) is the
 * caller's policy. The narrowed `@param` enforces the range statically.
 * `JsonSchemaExceptionHandlerInterface` receives this concrete subtype;
 * `catch (JsonSchemaException $e)` still matches.
 *
 * @psalm-import-type ServerErrorCode from Types
 */
final class JsonSchemaResponseException extends JsonSchemaException
{
    /**
     * @param ServerErrorCode $code
     *
     * @psalm-pure
     */
    public function __construct(string $message, int $code = Code::ERROR, JsonSchemaErrors|null $errors = null)
    {
        parent::__construct($message, $code, $errors);
    }
}
