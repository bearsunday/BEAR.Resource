<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\MethodException;
use BEAR\Resource\Exception\OutOfBoundsException;

abstract class AbstractRequest implements RequestInterface, \ArrayAccess, \IteratorAggregate
{
    const GET = 'get';
    const POST = 'post';
    const PUT = 'put';
    const PATCH = 'patch';
    const DELETE = 'delete';
    const HEAD = 'head';
    const OPTIONS = 'options';

    /**
     * URI
     *
     * @var string
     */
    public $uri;

    /**
     * Resource object
     *
     * @var \BEAR\Resource\ResourceObject
     */
    public $resourceObject;

    /**
     * Method
     *
     * @var string
     */
    public $method = '';

    /**
     * Query
     *
     * @var array
     */
    public $query = [];

    /**
     * Options
     *
     * @var array
     */
    public $options = [];

    /**
     * Request option (eager or lazy)
     *
     * @var string
     */
    public $in;

    /**
     * Links
     *
     * @var \BEAR\Resource\LinkType[]
     */
    public $links = [];

    /**
     * Request Result
     *
     * @var ResourceObject
     */
    protected $result;

    /**
     * @var InvokerInterface
     */
    protected $invoker;

    /**
     * @var LinkerInterface
     */
    private $linker;

    /**
     * @param InvokerInterface $invoker
     * @param ResourceObject   $ro
     * @param string           $method
     * @param array            $query
     * @param LinkType[]       $links
     */
    public function __construct(
        InvokerInterface $invoker,
        ResourceObject $ro = null,
        $method = Request::GET,
        array $query = [],
        array $links = [],
        LinkerInterface $linker = null
    ) {
        $this->invoker = $invoker;
        $this->resourceObject = $ro;
        if (! in_array(strtolower($method), [self::GET, self::POST, self::PUT, self::PATCH, self::DELETE, self::HEAD, self::OPTIONS])) {
            throw new MethodException($method, 400);
        }
        $this->method = $method;
        $this->query = $query;
        $this->links = $links;
        $this->linker = $linker;
    }

    /**
     *{@inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
        throw new OutOfBoundsException(__METHOD__ . ' is unavailable.', 400);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($offset)
    {
        unset($offset);
        throw new OutOfBoundsException(__METHOD__ . ' is unavailable.', 400);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            $this->invoke();

            return (string)$this->result;
        } catch (\Exception $e) {
            error_log($e);

            return '';
        }
    }

    /**
     * {@inheritDoc}
     */
    public function request()
    {
        if ($this->in !== 'eager') {
            return $this;
        }
        $this->result = $this->invoke($this);

        return $this->result;
    }

    /**
     * @return ResourceObject
     */
    private function invoke()
    {
        if (is_null($this->result)) {
            $this->result = $this->__invoke();
        }

        return $this->result;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(array $query = null)
    {
        if (!is_null($query)) {
            $this->query = array_merge($this->query, $query);
        }
        $result = ($this->linker) ? $result = $this->linker->invoke($this) : $this->invoker->invoke($this);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        $this->invoke();
        if (!isset($this->result->body[$offset])) {
            throw new OutOfBoundsException("[$offset] for object[" . get_class($this->result) . "]", 400);
        }

        return $this->result->body[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        $this->invoke();

        return isset($this->result->body[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        $this->invoke();
        $isArray = (is_array($this->result->body) || $this->result->body instanceof \Traversable);
        $iterator = $isArray ? new \ArrayIterator($this->result->body) : new \ArrayIterator([]);

        return $iterator;
    }

    /**
     * {@inheritdoc}
     */
    public function hash()
    {
        return md5(get_class($this->resourceObject) . $this->method . serialize($this->query) . serialize($this->links));
    }
}
