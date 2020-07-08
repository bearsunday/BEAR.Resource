<?php

declare(strict_types=1);

namespace BEAR\Resource\Exception;

use BadMethodCallException;
use BEAR\Resource\Code;
use Exception;

class BadRequestException extends BadMethodCallException implements ExceptionInterface
{
    public function __construct(string $message = '', int $code = Code::BAD_REQUEST, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
