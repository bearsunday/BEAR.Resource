<?php

declare(strict_types=1);

namespace BEAR\Resource\Exception;

use BEAR\Resource\Code;

class ResourceNotFoundException extends BadRequestException
{
    public function __construct($message = '', $code = Code::NOT_FOUND, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
