<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\JsonSchemaException;
use BEAR\Resource\Exception\JsonSchemaResponseException;

interface JsonSchemaExceptionHandlerInterface
{
    /**
     * Handle invalid JSON schema resource object
     *
     * The interceptor always delivers `JsonSchemaResponseException` (a subclass of
     * `JsonSchemaException`) here. The runtime parameter type stays on the parent
     * for backwards compatibility with existing implementations.
     *
     * @param JsonSchemaResponseException $e
     *
     * @return void
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    public function handle(ResourceObject $ro, JsonSchemaException $e, string $schemaFile);
}
