<?php

declare(strict_types=1);

namespace BEAR\Resource\Exception;

use BEAR\Resource\Code;

class BadRequestException extends \BadMethodCallException implements ExceptionInterface
{
    public function __construct(string $message = '', int $code = Code::BAD_REQUEST, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
