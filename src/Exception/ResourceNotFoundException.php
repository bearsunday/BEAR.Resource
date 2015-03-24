<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Exception;

use BEAR\Resource\Code;

class ResourceNotFoundException extends BadRequestException implements ExceptionInterface
{
    public function __construct($message = null, $code = Code::NOT_FOUND, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
