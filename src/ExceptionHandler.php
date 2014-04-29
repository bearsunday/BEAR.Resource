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
        error_log(
            sprintf(
                "%s(%s) in %s on line %s (%s)" . PHP_EOL . "%s",
                get_class($e),
                $e->getMessage(),
                __FILE__,
                __LINE__,
                $request->toUriWithMethod(),
                $e->getTraceAsString()
            )
        );

        throw $e;
    }
}
