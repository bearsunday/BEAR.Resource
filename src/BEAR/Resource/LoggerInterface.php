<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use IteratorAggregate;
use BEAR\Resource\AbstractObject as ResourceObject;

/**
 * Interface for resource logger
 */
interface LoggerInterface extends IteratorAggregate
{
    /**
     * Log
     *
     * @param RequestInterface $request
     * @param ResourceObject   $result
     *
     * @return void
     */
    public function log(RequestInterface $request, ResourceObject $result);

    /**
     * Set log writer
     *
     * @param LogWriterInterface $writer
     *
     * @return void
     */
    public function setWriter(LogWriterInterface $writer);

    /**
     * write log
     *
     * @return void
     */
    public function write();
}
