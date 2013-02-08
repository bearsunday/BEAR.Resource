<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use ArrayIterator;
use Countable;
use Ray\Di\Di\Scope;
use BEAR\Resource\AbstractObject as ResourceObject;
use Ray\Di\Di\Inject;

/**
 * Interface for resource logger
 *
 * @package BEAR.Resource
 *
 * @Scope("singleton")
 */
class Logger implements LoggerInterface, Countable
{
    const LOG_REQUEST = 0;
    const LOG_RESULT = 1;

    /**
     * Logs
     *
     * @var array
     */
    private $logs = [];

    /**
     * @var LogWriterInterface
     */
    private $writer;

    /**
     * Return new resource object instance
     *
     * {@inheritdoc}
     */
    public function log(RequestInterface $request, ResourceObject $result)
    {
        $this->logs[] = [
            self::LOG_REQUEST => $request,
            self::LOG_RESULT => $result
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @Inject(optional = true)
     */
    public function setWriter(LogWriterInterface $writer)
    {
        $this->writer = $writer;
    }

    /**
     * {@inheritdoc}
     */
    public function write()
    {
        if ($this->writer instanceof LogWriterInterface) {
            foreach ($this->logs as $log) {
                $this->writer->write($log[0], $log[1]);
            }
            $this->logs = [];
            return true;
        }
        return false;
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

    /**
     * @return int
     */
    public function count()
    {
        return count($this->logs);
    }
}
