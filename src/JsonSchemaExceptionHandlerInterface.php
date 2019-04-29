<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\JsonSchemaException;

interface JsonSchemaExceptionHandlerInterface
{
    /**
     * Handle invalid JSON schema resource object
     */
    public function handle(ResourceObject $ro, JsonSchemaException $e, string $schemaFile);
}
