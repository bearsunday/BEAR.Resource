<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\JsonSchemaException;

class JsonSchemaExceptionVoidHandler implements JsonSchemaExceptionHandlerInterface
{
    public function handle(ResourceObject $ro, JsonSchemaException $e, string $schemaFile)
    {
        throw $e;
    }
}
