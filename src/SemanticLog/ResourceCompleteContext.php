<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog;

use Koriym\SemanticLogger\AbstractContext;

final class ResourceCompleteContext extends AbstractContext
{
    /** @psalm-suppress InvalidClassConstantType */
    public const TYPE = 'bear_resource_complete';

    /** @psalm-suppress InvalidClassConstantType */
    public const SCHEMA_URL = 'file://' . __DIR__ . '/schema/bear-resource-complete.json';

    /** @param array<string, mixed> $body */
    public function __construct(
        public readonly string $resourceClass,
        public readonly string $method,
        public readonly int $code,
        public readonly array $body,
    ) {
    }
}
