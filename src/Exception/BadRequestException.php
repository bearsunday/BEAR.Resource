<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\Exception;

use BEAR\Resource\Code;

class BadRequestException extends \BadMethodCallException implements ExceptionInterface
{
    public function __construct($message = null, $code = Code::BAD_REQUEST, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
