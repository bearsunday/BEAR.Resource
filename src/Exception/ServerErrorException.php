<?php

declare(strict_types=1);

namespace BEAR\Resource\Exception;

use BEAR\Resource\Code;
use ErrorException;
use Throwable;

class ServerErrorException extends ErrorException implements ExceptionInterface
{
    public function __construct(string $message = '', int $code = Code::ERROR, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, 1, '', 0, $previous);
    }
}
