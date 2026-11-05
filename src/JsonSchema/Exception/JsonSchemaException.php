<?php

declare(strict_types=1);

namespace BEAR\Resource\Exception;

use BEAR\Resource\JsonSchema\JsonSchemaErrors;
use LogicException;

class JsonSchemaException extends LogicException implements ExceptionInterface
{
    /** @psalm-suppress UnusedProperty Read by the public getErrors() API, which only callers outside src use */
    private readonly JsonSchemaErrors $errors;

    /** @psalm-capabilities read-props */
    public function __construct(
        string $message,
        int $code = 0,
        JsonSchemaErrors|null $errors = null,
    ) {
        parent::__construct($message, $code);

        $this->errors = $errors ?? new JsonSchemaErrors([]);
    }

    public function getErrors(): JsonSchemaErrors
    {
        return $this->errors;
    }
}
