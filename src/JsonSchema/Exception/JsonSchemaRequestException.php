<?php

declare(strict_types=1);

namespace BEAR\Resource\Exception;

use BEAR\Resource\Code;
use BEAR\Resource\JsonSchema\JsonSchemaErrors;
use BEAR\Resource\Types;

/**
 * Thrown when a JSON Schema validation against a method's input parameters fails.
 *
 * Carries a 4xx-class `Code::*` value — the precise code (400, 401, 422, …) is the
 * caller's policy. The narrowed `@param` enforces the range statically.
 * `JsonSchemaRequestExceptionHandlerInterface` receives this concrete subtype;
 * `catch (JsonSchemaException $e)` still matches.
 *
 * @psalm-import-type ClientErrorCode from Types
 */
final class JsonSchemaRequestException extends JsonSchemaException
{
    /**
     * @param ClientErrorCode $code
     *
     * @psalm-capabilities read-props
     */
    public function __construct(string $message, int $code = Code::BAD_REQUEST, JsonSchemaErrors|null $errors = null)
    {
        parent::__construct($message, $code, $errors);
    }
}
