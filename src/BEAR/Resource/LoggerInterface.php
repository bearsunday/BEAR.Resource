<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use IteratorAggregate;

/**
 * Interface for resource logger
 *
 * @package BEAR.Resource
 */
interface LoggerInterface extends IteratorAggregate
{
    /**
     * Log
     *
     * @param RequestInterface $request
     * @param ObjectInterface  $result
     *
     * @return void
     */
    public function log(RequestInterface $request, ObjectInterface $result);
}
