<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\MethodException;
use BEAR\Resource\Exception\OutOfBoundsException;

/**
 * @property string $code
 * @property array  $headers
 * @property mixed  $body
 * @property string $view
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
     * @var ResourceObject
     */
    public $resourceObject;

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
     * @var LinkerInterface | null
     */
    private $linker;

    /**
     * @param InvokerInterface     $invoker
     * @param ResourceObject       $ro
     * @param string               $method
     * @param array                $query
     * @param array                $links
     * @param LinkerInterface|null $linker
     *
     * @throws MethodException
     */
    public function __construct(
        InvokerInterface $invoker,
        ResourceObject $ro,
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
            trigger_error($e->getMessage() . PHP_EOL . $e->getTraceAsString(), E_USER_ERROR);

            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(array $query = null) : ResourceObject
    {
        if (is_array($query)) {
            $this->query = array_merge($this->query, $query);
        }
        $this->resourceObject->uri->query = $this->query;
        if ($this->links && $this->linker instanceof LinkerInterface) {
            return $this->linker->invoke($this);
        }

        return $this->invoker->invoke($this);
    }

    /**
     * {@inheritdoc}
     */
    public function __get(string $name)
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
        if ($this->in === 'eager') {
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
    public function hash() : string
    {
        return md5(get_class($this->resourceObject) . $this->method . serialize($this->query) . serialize($this->links));
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        throw new \LogicException(__METHOD__ . ' not supported');
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        throw new \LogicException(__METHOD__ . ' not supported');
    }

    private function invoke() : ResourceObject
    {
        if ($this->result === null) {
            /* @noinspection ImplicitMagicMethodCallInspection */
            $this->result = ($this)();
        }

        return $this->result;
    }
}
