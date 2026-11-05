<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\JsonSchemaException;
use BEAR\Resource\Exception\JsonSchemaRequestException;

/** @psalm-import-type Query from Types */
interface JsonSchemaRequestExceptionHandlerInterface
{
    /**
     * Handle invalid request object
     *
     * The interceptor always delivers `JsonSchemaRequestException` (a subclass of
     * `JsonSchemaException`) here. The runtime parameter type stays on the parent
     * for backwards compatibility with existing implementations.
     *
     * @param Query                      $arguments
     * @param JsonSchemaRequestException $e
     *
     * @return void
     */
    public function handleRequestException(
        array $arguments,
        ResourceObject $ro,
        JsonSchemaException $e,
        string $schemaFile,
    );
}
