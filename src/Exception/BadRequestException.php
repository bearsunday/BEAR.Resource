<?php

declare(strict_types=1);

namespace BEAR\Resource\Exception;

use BadMethodCallException;
use BEAR\Resource\Code;
use Throwable;

class BadRequestException extends BadMethodCallException implements ExceptionInterface
{
    public function __construct(string $message = '', int $code = Code::BAD_REQUEST, Throwable|null $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
