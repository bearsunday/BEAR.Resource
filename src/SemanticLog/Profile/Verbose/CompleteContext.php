<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog\Profile\Verbose;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\SemanticLog\Profile\PhpProfile;
use BEAR\Resource\SemanticLog\Profile\Profile;
use BEAR\Resource\Types;
use JsonSerializable;
use Koriym\SemanticLogger\AbstractContext;
use Override;

/**
 * @psalm-import-type Headers from Types
 * @psalm-import-type HttpBody from Types
 */
final class CompleteContext extends AbstractContext implements JsonSerializable
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
    public readonly Profile $profile;

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

        // Stop profiling and capture final profile data
        $xhprofResult = $openContext->profile->xhprof?->stop($this->uri);
        $xdebugTrace = $openContext->profile->xdebug?->stop();
        $phpProfile = PhpProfile::capture();

        $this->profile = new Profile(
            xhprof: $xhprofResult,
            xdebug: $xdebugTrace,
            php: $phpProfile,
        );
    }

    public static function create(ResourceObject $resource, OpenContext $openContext): self
    {
        return new self($resource, $openContext);
    }

    /** @return array<string, mixed> */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'uri' => $this->uri,
            'code' => $this->code,
            'headers' => $this->headers,
            'body' => $this->body,
            'view' => $this->view,
            'profile' => $this->profile,
        ];
    }
}
