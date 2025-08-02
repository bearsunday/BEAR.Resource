<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog\Profile\Compact;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Types;
use Koriym\SemanticLogger\AbstractContext;

/**
 * @psalm-import-type Headers from Types
 * @psalm-import-type HttpBody from Types
 */
final class CompleteContext extends AbstractContext
{
    /** @psalm-suppress InvalidClassConstantType */
    public const TYPE = 'bear_resource_complete';

    /** @psalm-suppress InvalidClassConstantType */
    public const SCHEMA_URL = 'https://bearsunday.github.io/BEAR.Resource/schemas/complete-context.json';

    public readonly string $uri;
    public readonly int $code;

    /** @var Headers */
    public readonly array $headers;

    /** @var HttpBody  */
    public readonly mixed $body;
    public readonly string $view;

    public function __construct(ResourceObject $resource, OpenContext $openContext)
    {
        // Trigger rendering to get view
        $resourceString = (string) $resource;
        unset($resourceString);

        $this->uri = (string) $resource->uri;
        $this->code = $resource->code;
        $this->headers = $resource->headers;
        /** @psalm-suppress MixedAssignment */
        /** @phpstan-ignore-next-line */
        $this->body = $resource->body;
        /** @psalm-suppress PossiblyNullPropertyAssignmentValue */
        $this->view = $resource->view ?? '';
        unset($openContext);
    }

    public static function create(ResourceObject $resource, OpenContext $openContext): self
    {
        return new self($resource, $openContext);
    }
}
