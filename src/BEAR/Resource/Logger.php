<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use ArrayIterator;
use IteratorAggregate;
use Countable;
use Ray\Di\Di\Scope;

/**
 * Interface for resource logger
 *
 * @package BEAR.Resource
 *
 * @Scope("singleton")
 */
class Logger implements LoggerInterface, IteratorAggregate, Countable
{
    const LOG_REQUEST  = 0;
    const LOG_RESULT   = 1;

    /**
     * Logs
     *
     * @var array
     */
    private $logs = [];

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Return new resource object instance
     *
     * @param $request $request
     * @param mixed $result
     *
     * @return void
     */
    public function log(Request $request, $result)
    {
        $this->logs[] = [
            self::LOG_REQUEST => $request,
            self::LOG_RESULT => $result
        ];
    }

    /**
     * Return iterator
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->logs);
    }

    public function count()
    {
        return count($this->logs);
    }
}
