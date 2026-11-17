<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\JsonSchemaException;
use Override;

final class JsonSchemaRequestExceptionNullHandler implements JsonSchemaRequestExceptionHandlerInterface
{
    /**
     * {@inheritDoc}
     *
     * @psalm-pure
     */
    #[Override]
    public function handleRequestException(
        array $arguments,
        ResourceObject $ro,
        JsonSchemaException $e,
        string $schemaFile,
    ): never {
        throw $e;
    }
}
