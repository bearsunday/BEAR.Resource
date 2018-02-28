<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\MethodException;
use BEAR\Resource\Exception\OutOfBoundsException;

/**
 * @property string code
 * @property array headers
 * @property mixed body
 * @property string view
 */
abstract class AbstractRequest implements RequestInterface, \ArrayAccess, \IteratorAggregate, \Serializable
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
     * @var ResourceObject | null
     */
    protected $result;

    /**
     * @var InvokerInterface
     */
    protected $invoker;

    /**
     * @var LinkerInterface | null
     */
    private $linker;

    /**
     * @param InvokerInterface     $invoker
     * @param ResourceObject|null  $ro
     * @param string               $method
     * @param array                $query
     * @param array                $links
     * @param LinkerInterface|null $linker
     *
     * @throws MethodException
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
        if (! in_array(strtolower($method), [self::GET, self::POST, self::PUT, self::PATCH, self::DELETE, self::HEAD, self::OPTIONS], true)) {
            throw new MethodException($method, 400);
        }
        $this->method = $method;
        $this->query = $query;
        $this->links = $links;
        $this->linker = $linker;
    }

    public function __toString()
    {
        try {
            $this->invoke();

            return (string) $this->result;
        } catch (\Exception $e) {
            error_log($e);

            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(array $query = null)
    {
        if ($query !== null) {
            $this->query = array_merge($this->query, $query);
        }
        if ($this->links) {
            return $this->linker->invoke($this);
        }

        return $this->invoker->invoke($this);
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        $this->result = $this->invoke();

        return $this->result->$name;
    }

    /**
     *{@inheritdoc}
     *
     * @throws OutOfBoundsException
     */
    public function offsetSet($offset, $value)
    {
        throw new OutOfBoundsException(__METHOD__ . ' is unavailable.', 400);
    }

    /**
     * {@inheritdoc}
     *
     * @throws OutOfBoundsException
     */
    public function offsetUnset($offset)
    {
        unset($offset);
        throw new OutOfBoundsException(__METHOD__ . ' is unavailable.', 400);
    }

    /**
     * {@inheritdoc}
     */
    public function request()
    {
        if ($this->in == 'eager') {
            $this->result = $this->invoke();

            return $this->result;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws OutOfBoundsException
     */
    public function offsetGet($offset)
    {
        $this->invoke();
        if (! isset($this->result->body[$offset])) {
            throw new OutOfBoundsException("[$offset] for object[" . get_class($this->result) . ']', 400);
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

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize($this->invoke());
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        return unserialize($serialized);
    }

    private function invoke() : ResourceObject
    {
        if ($this->result === null) {
            /* @noinspection ImplicitMagicMethodCallInspection */
            $this->result = $this->__invoke();
        }

        return $this->result;
    }
}
