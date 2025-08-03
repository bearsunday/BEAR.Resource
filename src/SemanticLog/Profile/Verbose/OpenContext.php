<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog\Profile\Verbose;

use BEAR\Resource\AbstractRequest;
use BEAR\Resource\SemanticLog\Profile\PhpProfile;
use BEAR\Resource\SemanticLog\Profile\Profile;
use BEAR\Resource\SemanticLog\Profile\XdebugTrace;
use BEAR\Resource\SemanticLog\Profile\XHProfResult;
use JsonSerializable;
use Koriym\SemanticLogger\AbstractContext;
use Override;

use function spl_object_hash;
use function strtoupper;
use function uniqid;

final class OpenContext extends AbstractContext implements JsonSerializable
{
    /** @psalm-suppress InvalidClassConstantType */
    public const TYPE = 'bear_resource_request';

    /** @psalm-suppress InvalidClassConstantType */
    public const SCHEMA_URL = 'https://bearsunday.github.io/BEAR.Resource/schemas/open-context.json';

    public readonly string $method;
    public readonly string $uri;
    public readonly Profile $profile;

    /** @var array<string, string|null> */
    private static array $xdebugIdMap = [];

    public function __construct(AbstractRequest $request)
    {
        $this->method = strtoupper($request->method);
        $this->uri = $request->toUri();

        // Start profiling and capture initial profile data
        $xhprofResult = XHProfResult::start();
        $xdebugTrace = XdebugTrace::start();
        $phpProfile = PhpProfile::capture();

        $this->profile = new Profile(
            xhprof: $xhprofResult,
            xdebug: $xdebugTrace,
            php: $phpProfile,
        );

        // Always generate an ID for profiling context, regardless of Xdebug availability
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
            'profile' => $this->profile,
        ];
    }
}
