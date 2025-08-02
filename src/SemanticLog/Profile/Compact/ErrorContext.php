<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog\Profile\Compact;

use Koriym\SemanticLogger\AbstractContext;
use Throwable;

use function crc32;
use function dechex;

final class ErrorContext extends AbstractContext
{
    /** @psalm-suppress InvalidClassConstantType */
    public const TYPE = 'bear_resource_error';

    /** @psalm-suppress InvalidClassConstantType */
    public const SCHEMA_URL = 'https://bearsunday.github.io/BEAR.Resource/schemas/error-context.json';

    public readonly string $exceptionId;
    public readonly string $exceptionAsString;

    public function __construct(
        Throwable $exception,
        string $exceptionId = '',
        ?OpenContext $openContext = null,
    ) {
        $this->exceptionAsString = (string) $exception;
        $this->exceptionId = $exceptionId !== '' ? $exceptionId : $this->createExceptionId();

        // OpenContext is intentionally unused in Compact profile (no profiling)
        unset($openContext);
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
}
