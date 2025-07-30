<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog;

use BEAR\Resource\ResourceObject;
use Koriym\SemanticLogger\AbstractContext;

use function strtoupper;

/** @deprecated Use BEAR\Resource\SemanticLog\Profile\Compact\CompleteContext instead */
final class ResourceCompleteContext extends AbstractContext
{
    /** @psalm-suppress InvalidClassConstantType */
    public const TYPE = 'bear_resource_complete';

    /** @psalm-suppress InvalidClassConstantType */
    public const SCHEMA_URL = 'https://bearsunday.github.io/BEAR.Resource/schemas/bear-resource-complete.json';

    public readonly string $uri;
    public readonly string $method;
    public readonly int $code;

    /** @var array<string, string> */
    public readonly array $headers;
    public readonly mixed $body;
    public readonly string $view;

    public function __construct(ResourceObject $resource, string $method)
    {
        // Trigger rendering to get view
        $resourceString = (string) $resource;
        unset($resourceString);

        $this->uri = (string) $resource->uri;
        $this->method = strtoupper($method);
        $this->code = $resource->code;
        $this->headers = $resource->headers;
        $this->body = $resource->body;
        /** @psalm-suppress PossiblyNullPropertyAssignmentValue */
        $this->view = $resource->view ?? '';
    }
}
