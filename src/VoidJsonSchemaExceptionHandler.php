<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\JsonSchemaException;

class VoidJsonSchemaExceptionHandler implements JsonSchemaExceptionHandlerInterface
{
    public function handle(ResourceObject $ro, JsonSchemaException $e)
    {
        throw $e;
    }
}
