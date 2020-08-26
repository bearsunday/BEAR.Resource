<?php

declare(strict_types=1);

namespace BEAR\Resource\Exception;

use BEAR\Resource\Code;
use Throwable;

class ResourceNotFoundException extends BadRequestException
{
    public function __construct(string $message = '', int $code = Code::NOT_FOUND, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
