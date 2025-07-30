<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog;

use Koriym\SemanticLogger\AbstractContext;

use function crc32;
use function dechex;

/** @deprecated Use BEAR\Resource\SemanticLog\Profile\Compact\ErrorContext instead */
final class ResourceErrorContext extends AbstractContext
{
    /** @psalm-suppress InvalidClassConstantType */
    public const TYPE = 'bear_resource_error';

    /** @psalm-suppress InvalidClassConstantType */
    public const SCHEMA_URL = 'https://bearsunday.github.io/BEAR.Resource/schemas/bear-resource-error.json';

    public readonly string $exceptionId;

    public function __construct(public readonly string $exceptionClass, public readonly string $exceptionMessage, string $exceptionId = '')
    {
        $this->exceptionId = $exceptionId !== '' ? $exceptionId : $this->createExceptionId();
    }

    private function createExceptionId(): string
    {
        $content = $this->exceptionClass . ':' . $this->exceptionMessage;
        $crc = crc32($content);
        $crcHex = dechex($crc & 0xFFFFFFFF);

        return 'e-bear-resource-' . $crcHex;
    }
}
