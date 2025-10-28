<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog\Profile\Compact;

use BEAR\Resource\AbstractRequest;
use Koriym\SemanticLogger\AbstractContext;

use function strtoupper;

final class OpenContext extends AbstractContext
{
    /** @psalm-suppress InvalidClassConstantType */
    public const TYPE = 'bear_resource_request';

    /** @psalm-suppress InvalidClassConstantType */
    public const SCHEMA_URL = 'https://bearsunday.github.io/BEAR.Resource/schemas/open-context.json';

    public readonly string $method;
    public readonly string $uri;

    public function __construct(AbstractRequest $request)
    {
        $this->method = strtoupper($request->method);
        $this->uri = $request->toUri();
    }

    public static function create(AbstractRequest $request): self
    {
        return new self($request);
    }
}
