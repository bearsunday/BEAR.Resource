<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog\Profile\Verbose;

use BEAR\Resource\AbstractRequest;
use JsonSerializable;
use Koriym\SemanticLogger\AbstractContext;
use Override;

use function strtolower;
use function strtoupper;
use function ucfirst;
use function uniqid;

final class OpenContext extends AbstractContext implements JsonSerializable
{
    /** @psalm-suppress InvalidClassConstantType */
    public const TYPE = 'bear_resource_request';

    /** @psalm-suppress InvalidClassConstantType */
    public const SCHEMA_URL = 'https://bearsunday.github.io/BEAR.Resource/schemas/open-context.json';

    public readonly string $method;
    public readonly string $uri;

    /** @var array{string, string} */
    public readonly array $call;

    public function __construct(AbstractRequest $request)
    {
        $this->method = strtoupper($request->method);
        $this->uri = $request->toUri();
        $this->call = [
            $request->resourceObject::class,
            'on' . ucfirst(strtolower($request->method)),
        ];

        // Start XdebugTrace for profiling (handled internally)
    }

    public static function create(AbstractRequest $request): self
    {
        return new self($request);
    }

    public function getXdebugId(): string
    {
        return uniqid('profile_', true);
    }

    /** @return array<string, mixed> */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'method' => $this->method,
            'uri' => $this->uri,
            'call' => $this->call,
        ];
    }
}
