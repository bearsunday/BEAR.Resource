<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog\Profile\Verbose;

use BEAR\Resource\AbstractRequest;
use JsonSerializable;
use Koriym\SemanticLogger\AbstractContext;
use Koriym\SemanticLogger\Profiler\XdebugTrace;
use Koriym\SemanticLogger\Profiler\XHProfResult;
use Override;

use function spl_object_hash;
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

    /** @var array<string, string|null> */
    private static array $xdebugIdMap = [];

    public function __construct(AbstractRequest $request)
    {
        $this->method = strtoupper($request->method);
        $this->uri = $request->toUri();
        $this->call = [
            $request->resourceObject::class,
            'on' . ucfirst(strtolower($request->method)),
        ];

        // Start profiling (but don't capture data yet - that's for close)
        XHProfResult::start();
        XdebugTrace::start();

        // Generate an ID for profiling context
        $xdebugId = uniqid('profile_', true);
        self::$xdebugIdMap[spl_object_hash($this)] = $xdebugId;
    }

    public static function create(AbstractRequest $request): self
    {
        return new self($request);
    }

    public function getXdebugId(): ?string
    {
        return self::$xdebugIdMap[spl_object_hash($this)] ?? null;
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
