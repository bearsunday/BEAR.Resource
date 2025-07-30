<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog;

use Koriym\SemanticLogger\AbstractContext;

use function strtoupper;

/** @deprecated Use BEAR\Resource\SemanticLog\Profile\Compact\OpenContext instead */
final class ResourceOpenContext extends AbstractContext
{
    /** @psalm-suppress InvalidClassConstantType */
    public const TYPE = 'bear_resource_request';

    /** @psalm-suppress InvalidClassConstantType */
    public const SCHEMA_URL = 'https://bearsunday.github.io/BEAR.Resource/schemas/bear-resource-request.json';

    public readonly string $method;

    /** @param array<string, mixed> $query */
    public function __construct(public readonly string $uri, string $method, public readonly array $query = [])
    {
        $this->method = strtoupper($method);
    }
}
