<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog\Profile\Verbose;

use JsonSerializable;
use Koriym\SemanticLogger\AbstractContext;
use Override;
use Throwable;

use function crc32;
use function dechex;
use function file_exists;
use function file_put_contents;
use function function_exists;
use function is_string;
use function serialize;
use function sprintf;
use function str_replace;
use function sys_get_temp_dir;
use function uniqid;
use function xdebug_stop_trace;
use function xhprof_disable;

final class ErrorContext extends AbstractContext implements JsonSerializable
{
    /** @psalm-suppress InvalidClassConstantType */
    public const TYPE = 'bear_resource_error';

    /** @psalm-suppress InvalidClassConstantType */
    public const SCHEMA_URL = 'https://bearsunday.github.io/BEAR.Resource/schemas/error-context.json';

    public readonly string $exceptionId;
    public readonly string $exceptionAsString;
    public ?string $xhprofFile;
    public ?string $xdebugTraceFile;

    public function __construct(
        Throwable $exception,
        string $exceptionId = '',
        ?OpenContext $openContext = null,
    ) {
        $this->exceptionAsString = (string) $exception;
        $this->exceptionId = $exceptionId !== '' ? $exceptionId : $this->createExceptionId();

        // Initialize profiling files
        $this->xhprofFile = null;
        $this->xdebugTraceFile = null;

        if ($openContext === null) {
            return;
        }

        // Stop profiling and save files
        if (function_exists('xhprof_disable')) {
            $xhprofData = xhprof_disable();
            $filename = sprintf(
                '%s/xhprof_%s_%s.xhprof',
                sys_get_temp_dir(),
                str_replace(['/', ':', '?'], '_', $openContext->uri),
                uniqid('', true),
            );

            if (file_put_contents($filename, serialize($xhprofData)) !== false) {
                $this->xhprofFile = $filename;
            }
        }

        // Handle Xdebug trace
        $xdebugId = $openContext->getXdebugId();
        if ($xdebugId === null || ! function_exists('xdebug_stop_trace')) {
            return; // @codeCoverageIgnore
        }

        /** @var string|null $traceFile */
        $traceFile = @xdebug_stop_trace(); // @phpstan-ignore-line
        if (! is_string($traceFile) || ! file_exists($traceFile)) {
            return;
        }

        $this->xdebugTraceFile = $traceFile; // @codeCoverageIgnore
    }

    public static function create(
        Throwable $exception,
        string $exceptionId = '',
        ?OpenContext $openContext = null,
    ): self {
        return new self($exception, $exceptionId, $openContext);
    }

    private function createExceptionId(): string
    {
        $crc = crc32($this->exceptionAsString);
        $crcHex = dechex($crc & 0xFFFFFFFF); // Ensure positive hex value

        return 'e-bear-resource-' . $crcHex;
    }

    /** @return array<string, mixed> */
    #[Override]
    public function jsonSerialize(): array
    {
        $data = [
            'exceptionId' => $this->exceptionId,
            'exceptionAsString' => $this->exceptionAsString,
        ];

        // Only include profiling files if they exist
        if ($this->xhprofFile !== null) {
            $data['xhprofFile'] = $this->xhprofFile;
        }

        if ($this->xdebugTraceFile !== null) {
            $data['xdebugTraceFile'] = $this->xdebugTraceFile; // @codeCoverageIgnore
        }

        return $data;
    }
}
