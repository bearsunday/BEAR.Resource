<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog\Profile\Verbose;

use BEAR\Resource\AbstractRequest;
use JsonSerializable;
use Koriym\SemanticLogger\AbstractContext;
use Override;

use function extension_loaded;
use function function_exists;
use function ini_get;
use function rtrim;
use function spl_object_hash;
use function strtoupper;
use function sys_get_temp_dir;
use function uniqid;
use function xdebug_start_trace;
use function xdebug_stop_trace;
use function xhprof_enable;

use const XHPROF_FLAGS_CPU;
use const XHPROF_FLAGS_MEMORY;
use const XHPROF_FLAGS_NO_BUILTINS;

final class OpenContext extends AbstractContext implements JsonSerializable
{
    /** @psalm-suppress InvalidClassConstantType */
    public const TYPE = 'bear_resource_request';

    /** @psalm-suppress InvalidClassConstantType */
    public const SCHEMA_URL = 'https://bearsunday.github.io/BEAR.Resource/schemas/open-context.json';

    public readonly string $method;
    public readonly string $uri;

    /** @var array<string, string|null> */
    private static array $xdebugIdMap = [];

    public function __construct(AbstractRequest $request)
    {
        $this->method = strtoupper($request->method);
        $this->uri = $request->toUri();

        // if xhprof is enabled, start profiling
        if (function_exists('xhprof_enable')) {
            xhprof_enable(XHPROF_FLAGS_NO_BUILTINS | XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);
        }

        // Always generate an ID for profiling context, regardless of Xdebug availability
        $xdebugId = uniqid('profile_', true);
        self::$xdebugIdMap[spl_object_hash($this)] = $xdebugId;

        if (! extension_loaded('xdebug') || ! function_exists('xdebug_start_trace')) {
            return; // @codeCoverageIgnore
        }

        // Stop any existing trace first to ensure clean start
        @xdebug_stop_trace();

        // Use full path for trace file to ensure consistency with xhprofFile
        $outputDir = ini_get('xdebug.output_dir');
        if ($outputDir === false) {
            $outputDir = sys_get_temp_dir(); // @codeCoverageIgnore
        }

        $traceFilePrefix = rtrim($outputDir, '/') . '/' . $xdebugId;
        @xdebug_start_trace($traceFilePrefix);
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
        ];
    }
}
