<?php
/**
 * This file is part of the BEAR.Sunday package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\Exception;

use BEAR\Resource\Code;

class ResourceNotFoundException extends BadRequestException
{
    public function __construct($message = null, $code = Code::NOT_FOUND, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
