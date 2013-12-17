<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * Resource request invoker exception handler
 */
interface ExceptionHandlerInterface
{
    /**
     * Handle exception
     *
     * @param \Exception $e
     *
     * @return void
     */
    public function handle(\Exception $e);
}
