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
        error_log($request->toUriWithMethod());
        error_log(get_class($e) . ' in ' . __FILE__ . ' on line ' . __LINE__ . ' ' . $e);

        throw $e;
    }
}
