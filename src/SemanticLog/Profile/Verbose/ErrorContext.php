<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog\Profile\Verbose;

use BEAR\Resource\SemanticLog\Profile\PhpProfile;
use BEAR\Resource\SemanticLog\Profile\Profile;
use BEAR\Resource\SemanticLog\Profile\XdebugTrace;
use BEAR\Resource\SemanticLog\Profile\XHProfResult;
use JsonSerializable;
use Koriym\SemanticLogger\AbstractContext;
use Override;
use Throwable;

use function crc32;
use function dechex;

final class ErrorContext extends AbstractContext implements JsonSerializable
{
    /** @psalm-suppress InvalidClassConstantType */
    public const TYPE = 'bear_resource_error';

    /** @psalm-suppress InvalidClassConstantType */
    public const SCHEMA_URL = 'https://bearsunday.github.io/BEAR.Resource/schemas/error-context.json';

    public readonly string $exceptionId;
    public readonly string $exceptionAsString;
    public readonly Profile $profile;

    public function __construct(
        Throwable $exception,
        string $exceptionId = '',
        ?OpenContext $openContext = null,
    ) {
        $this->exceptionAsString = (string) $exception;
        $this->exceptionId = $exceptionId !== '' ? $exceptionId : $this->createExceptionId();

        if ($openContext === null) {
            // Create empty profile if no open context provided
            $this->profile = new Profile();

            return;
        }

        // Stop profiling and capture final profile data
        // Note: start() was called in OpenContext, now we stop and capture data
        $xhprofResult = (new XHProfResult())->stop($openContext->uri);
        $xdebugTrace = (new XdebugTrace())->stop();
        $phpProfile = PhpProfile::capture();

        $this->profile = new Profile(
            xhprof: $xhprofResult,
            xdebug: $xdebugTrace,
            php: $phpProfile,
        );
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
        return [
            'exceptionId' => $this->exceptionId,
            'exceptionAsString' => $this->exceptionAsString,
            'profile' => $this->profile,
        ];
    }
}
