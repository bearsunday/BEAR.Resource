<?php

declare(strict_types=1);

namespace BEAR\Resource\Exception;

use BEAR\Resource\Code;
use Throwable;

final class ResourceNotFoundException extends BadRequestException
{
    /** @psalm-pure */
    public function __construct(string $message = '', int $code = Code::NOT_FOUND, Throwable|null $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
