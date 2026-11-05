<?php

declare(strict_types=1);

namespace BEAR\Resource\Exception;

use BEAR\Resource\Code;
use RuntimeException;
use Throwable;

final class HttpRequestException extends RuntimeException implements ExceptionInterface
{
    /** @psalm-capabilities read-props */
    public function __construct(string $message = '', int $code = Code::ERROR, Throwable|null $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
