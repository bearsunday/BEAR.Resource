<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Di\Di\ImplementedBy;

/**
 * Interface for resource logger
 *
 * @package BEAR.Resource
 *
 * @ImplementedBy("BEAR\Resource\Logger")
 *
 */
interface LoggerInterface
{
    /**
     * Log
     *
     * @param Request $request
     * @param mixed   $result
     *
     * @return void
     */
    public function log(Request $request, $result);
}
