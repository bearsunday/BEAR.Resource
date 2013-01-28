<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Di\Di\ImplementedBy;
use IteratorAggregate;

/**
 * Interface for resource logger
 *
 * @package BEAR.Resource
 *
 * @ImplementedBy("BEAR\Resource\Logger")
 *
 */
interface LoggerInterface extends IteratorAggregate
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
