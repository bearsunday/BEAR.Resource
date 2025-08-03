<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog\Profile;

use JsonSerializable;
use Override;

use function extension_loaded;
use function file_exists;
use function function_exists;
use function getenv;
use function ini_get;
use function restore_error_handler;
use function rtrim;
use function set_error_handler;
use function str_contains;
use function sys_get_temp_dir;
use function uniqid;
use function xdebug_get_tracefile_name;
use function xdebug_start_trace;
use function xdebug_stop_trace;

use const E_NOTICE;

final class XdebugTrace implements JsonSerializable
{
    private ?string $traceId = null;

    public function __construct(
        public readonly ?string $file = null,
    ) {
    }

    public static function start(): self
    {
        if (! extension_loaded('xdebug') || ! function_exists('xdebug_start_trace')) {
            return new self(); // @codeCoverageIgnore
        }

        // Check if Xdebug trace functionality is properly configured
        $envMode = getenv('XDEBUG_MODE');
        $iniMode = ini_get('xdebug.mode');
        $xdebugMode = $envMode !== false ? $envMode : ($iniMode !== false ? $iniMode : '');
        if (! str_contains($xdebugMode, 'trace')) {
            return new self(); // @codeCoverageIgnore
        }

        // Check if trace is already running (due to xdebug.start_with_request=yes)
        $existingTrace = null;
        if (function_exists('xdebug_get_tracefile_name')) {
            $existingTrace = xdebug_get_tracefile_name(); // @codeCoverageIgnore
        }

        if ($existingTrace !== null) {
            // Trace already started by xdebug.start_with_request, use existing file
            return new self($existingTrace); // @codeCoverageIgnore
        }

        $instance = new self();
        $instance->traceId = uniqid('profile_', true);

        // Use full path for trace file to ensure consistency with xhprofFile
        $outputDir = ini_get('xdebug.output_dir');
        if ($outputDir === false) {
            $outputDir = sys_get_temp_dir(); // @codeCoverageIgnore
        }

        $traceFilePrefix = rtrim($outputDir, '/') . '/' . $instance->traceId;
        xdebug_start_trace($traceFilePrefix); // @codeCoverageIgnore

        // Note: Return value is void, trace may fail silently if already started elsewhere
        return $instance;
    }

    public function stop(): self
    {
        if (! $this->canStopTrace()) {
            return new self(); // @codeCoverageIgnore
        }

        return $this->performStopTrace(); // @codeCoverageIgnore
    }

    private function canStopTrace(): bool
    {
        if ($this->traceId === null || ! function_exists('xdebug_stop_trace')) {
            return false; // @codeCoverageIgnore
        }

        // Check if Xdebug trace functionality is properly configured
        $envMode = getenv('XDEBUG_MODE');
        $iniMode = ini_get('xdebug.mode');
        $xdebugMode = $envMode !== false ? $envMode : ($iniMode !== false ? $iniMode : '');

        return str_contains($xdebugMode, 'trace'); // @codeCoverageIgnore
    }

    private function performStopTrace(): self
    {
        // Try to stop trace and get the trace file path
        // Suppress "Function trace was not started" error for graceful handling
        set_error_handler(static function (int $errno, string $errstr): bool {
            // Ignore specific xdebug trace errors (only handle E_NOTICE)
            return $errno === E_NOTICE && str_contains($errstr, 'Function trace was not started');
        });

        try {
            xdebug_stop_trace(); // @codeCoverageIgnore - returns void
            // Try to get the trace file name if available
            $traceFile = function_exists('xdebug_get_tracefile_name') ? xdebug_get_tracefile_name() : false; // @codeCoverageIgnore
        } finally {
            restore_error_handler();
        }

        if ($traceFile === false || ! file_exists($traceFile)) {
            return new self(); // @codeCoverageIgnore
        }

        return new self($traceFile);
    }

    /** @return array<string, mixed> */
    #[Override]
    public function jsonSerialize(): array
    {
        if ($this->file === null) {
            return [];
        }

        return ['file' => $this->file];
    }
}
