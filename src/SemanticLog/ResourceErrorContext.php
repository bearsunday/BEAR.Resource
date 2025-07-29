<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog;

use Koriym\SemanticLogger\AbstractContext;

final class ResourceErrorContext extends AbstractContext
{
    /** @psalm-suppress InvalidClassConstantType */
    public const TYPE = 'bear_resource_error';

    /** @psalm-suppress InvalidClassConstantType */
    public const SCHEMA_URL = 'file://' . __DIR__ . '/schema/bear-resource-error.json';

    public function __construct(
        public readonly string $resourceClass,
        public readonly string $method,
        public readonly string $exceptionClass,
        public readonly string $exceptionMessage,
    ) {
    }
}
