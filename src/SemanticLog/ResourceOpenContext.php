<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog;

use Koriym\SemanticLogger\AbstractContext;

final class ResourceOpenContext extends AbstractContext
{
    /** @psalm-suppress InvalidClassConstantType */
    public const TYPE = 'bear_resource_request';

    /** @psalm-suppress InvalidClassConstantType */
    public const SCHEMA_URL = 'https://bearsunday.github.io/BEAR.Resource/schemas/bear-resource-request.json';

    /** @param array<string, mixed> $query */
    public function __construct(
        public readonly string $uri,
        public readonly string $method,
        public readonly array $query,
    ) {
    }
}
