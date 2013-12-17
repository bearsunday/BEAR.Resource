<?php

namespace BEAR\Resource;

/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
class ExceptionHandler implements ExceptionHandlerInterface
{
    public function handle(\Exception $e)
    {
        throw $e;
    }
}
