<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog;

use BEAR\Resource\ResourceObject;
use Koriym\SemanticLogger\AbstractContext;

final class ResourceCompleteContext extends AbstractContext
{
    /** @psalm-suppress InvalidClassConstantType */
    public const TYPE = 'bear_resource_complete';

    /** @psalm-suppress InvalidClassConstantType */
    public const SCHEMA_URL = 'https://bearsunday.github.io/BEAR.Resource/schemas/bear-resource-complete.json';

    public readonly string $uri;
    public readonly int $code;

    /** @var array<string, mixed> */
    public readonly array $headers;
    public readonly mixed $body;
    public readonly mixed $view;

    public function __construct(
        ResourceObject $resource,
        public readonly string $method,
    ) {
        // Trigger rendering to get view
        $resourceString = (string) $resource;
        unset($resourceString);

        $this->uri = (string) $resource->uri;
        $this->code = $resource->code;
        $this->headers = $resource->headers;
        $this->body = $resource->body;
        $this->view = $resource->view;
    }
}
