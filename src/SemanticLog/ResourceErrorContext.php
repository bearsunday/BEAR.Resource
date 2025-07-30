<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog;

use Koriym\SemanticLogger\AbstractContext;

final class ResourceErrorContext extends AbstractContext
{
    /** @psalm-suppress InvalidClassConstantType */
    public const TYPE = 'bear_resource_error';

    /** @psalm-suppress InvalidClassConstantType */
    public const SCHEMA_URL = 'https://bearsunday.github.io/BEAR.Resource/schemas/bear-resource-error.json';

    public readonly string $exceptionId;

    public function __construct(
        public readonly string $exceptionClass,
        public readonly string $exceptionMessage,
        string $exceptionId = '',
    ) {
        $this->exceptionId = $exceptionId !== '' ? $exceptionId : $this->createExceptionId();
    }

    private function createExceptionId(): string
    {
        $crc = crc32($this->exceptionClass);
        $crcHex = dechex($crc & 0xFFFFFFFF); // Ensure positive hex value
        
        // Use fixed ID in test environment for reproducible tests
        if (defined('PHPUNIT_COMPOSER_INSTALL') || (isset($GLOBALS['_composer_autoload_path']) && str_contains($GLOBALS['_composer_autoload_path'], 'phpunit'))) {
            static $counter = 0;
            return 'e-bear-resource-test-' . sprintf('%03d', ++$counter) . '-' . $crcHex;
        }
        
        return 'e-bear-resource-' . uniqid() . '-' . $crcHex;
    }
}
