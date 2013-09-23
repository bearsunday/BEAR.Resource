<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Any\Serializer\SerializeInterface;
use Any\Serializer\Serializer;
use ArrayIterator;
use Countable;
use Ray\Di\Di\Scope;
use Serializable;
use Ray\Di\Di\Inject;

/**
 * Resource logger
 */
class Logger implements LoggerInterface, Countable, Serializable
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
     * @var SerializeInterface
     */
    private $serializer;

    /**
     * @param SerializeInterface $serializer
     *
     * @Inject(optional=true)
     */
    public function __construct(SerializeInterface $serializer = null)
    {
        $this->serializer = $serializer ?: new Serializer;
    }

    /**
     * Return new resource object instance
     *
     * {@inheritDoc}
     */
    public function log(RequestInterface $request, ResourceObject $result)
    {
        $this->logs[] = [
            self::LOG_REQUEST => $request,
            self::LOG_RESULT => $result
        ];
    }

    /**
     * {@inheritDoc}
     *
     * @Inject(optional = true)
     */
    public function setWriter(LogWriterInterface $writer)
    {
        $this->writer = $writer;
    }

    /**
     * {@inheritDoc}
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

    public function serialize()
    {
        unset($this->logs);
        return serialize([$this->writer, $this->serializer]);
    }

    public function unserialize($data)
    {
        list($this->writer, $this->serializer) = unserialize($data);
    }
}
