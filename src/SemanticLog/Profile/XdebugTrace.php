<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog\Profile;

use JsonSerializable;
use Override;

use function extension_loaded;
use function file_exists;
use function file_get_contents;
use function function_exists;
use function getenv;
use function ini_get;
use function is_string;
use function restore_error_handler;
use function rtrim;
use function set_error_handler;
use function str_contains;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use function xdebug_get_tracefile_name;
use function xdebug_start_trace;
use function xdebug_stop_trace;

use const E_NOTICE;

final class XdebugTrace implements JsonSerializable
{
    private ?string $traceId = null;

    public function __construct(
        public readonly ?string $content = null,
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

        // Always start our own trace to ensure we have control over the file format
        // Stop any existing trace first to ensure we get a fresh start
        if (function_exists('xdebug_stop_trace')) {
            @xdebug_stop_trace(); // @codeCoverageIgnore - suppress errors if not running
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
        if (! function_exists('xdebug_stop_trace')) {
            return false; // @codeCoverageIgnore
        }

        // Check if Xdebug trace functionality is properly configured
        $envMode = getenv('XDEBUG_MODE');
        $iniMode = ini_get('xdebug.mode');
        $xdebugMode = $envMode !== false ? $envMode : ($iniMode !== false ? $iniMode : '');

        if (! str_contains($xdebugMode, 'trace')) {
            return false; // @codeCoverageIgnore
        }

        // Can stop if we started the trace ourselves, OR if there's an existing trace running
        return $this->traceId !== null || function_exists('xdebug_get_tracefile_name');
    }

    private function performStopTrace(): self
    {
        // If we already have content (from existing trace), preserve it
        if ($this->content !== null) {
            return new self($this->content); // @codeCoverageIgnore
        }

        // Try to stop trace and get the trace file path
        // Suppress "Function trace was not started" error for graceful handling
        set_error_handler(static function (int $errno, string $errstr): bool {
            // Ignore specific xdebug trace errors (only handle E_NOTICE)
            return $errno === E_NOTICE && str_contains($errstr, 'Function trace was not started');
        });

        try {
            // Get the trace file name BEFORE stopping the trace
            $traceFile = function_exists('xdebug_get_tracefile_name') ? xdebug_get_tracefile_name() : false; // @codeCoverageIgnore
            xdebug_stop_trace(); // @codeCoverageIgnore - returns void
        } finally {
            restore_error_handler();
        }

        if ($traceFile === false || ! is_string($traceFile) || ! file_exists($traceFile)) {
            return new self(); // @codeCoverageIgnore
        }

        // Read trace content and delete file for self-contained implementation
        $content = file_get_contents($traceFile);
        if ($content !== false) {
            @unlink($traceFile); // Clean up trace file
        }

        return new self($content !== false ? $content : null);
    }

    /** @return array<string, mixed> */
    #[Override]
    public function jsonSerialize(): array
    {
        if ($this->content === null) {
            return [];
        }

        return [
            'data' => $this->content,
            'spec_url' => 'https://xdebug.org/docs/trace#Output-Formats',
        ];
    }
}
