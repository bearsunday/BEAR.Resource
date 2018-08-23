<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\Exception;

use BEAR\Resource\Code;

class ResourceNotFoundException extends BadRequestException
{
    public function __construct($message = '', $code = Code::NOT_FOUND, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
