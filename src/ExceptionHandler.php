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
    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function handle(\Exception $e, Request $request)
    {
        throw $e;
    }
}
