<?php

declare(strict_types=1);

namespace BEAR\Resource;

use ArrayAccess;
use ArrayIterator;
use BEAR\Resource\Exception\MethodException;
use BEAR\Resource\Exception\OutOfBoundsException;
use IteratorAggregate;
use Serializable;

/**
 * @property string $code
 * @property array  $headers
 * @property mixed  $body
 * @property string $view
 *
 * @implements IteratorAggregate<string|null, mixed>
 * @implements ArrayAccess<string, mixed>
 */
abstract class AbstractRequest implements RequestInterface, ArrayAccess, IteratorAggregate, Serializable
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
     * @var array<string, mixed>
     */
    public $query = [];

    /**
     * Options
     *
     * @var array<mixed>
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
     * @var LinkType[]
     */
    public $links = [];

    /**
     * @var ResourceObject<string, mixed>
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
     * @var null|LinkerInterface
     */
    private $linker;

    /**
     * @param array<string, mixed> $query
     * @param array<int, LinkType> $links
     *
     * @throws MethodException
     */
    public function __construct(
        InvokerInterface $invoker,
        ResourceObject $ro,
        string $method = Request::GET,
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
     *
     * @param array<string, mixed> $query
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

        return clone $this->invoker->invoke($this);
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string, string>|int
     */
    public function __get(string $name)
    {
        $this->result = $this->invoke();

        return $this->result->{$name};
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
            throw new OutOfBoundsException("[${offset}] for object[" . get_class($this->result) . ']', 400);
        }

        return $this->result->body[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset) : bool
    {
        $this->invoke();

        return isset($this->result->body[$offset]);
    }

    /**
     * Invoke resource request then return resource body iterator
     *
     * @return ArrayIterator<null|string, mixed>
     */
    public function getIterator() : ArrayIterator
    {
        $this->invoke();
        $isArray = (is_array($this->result->body) || $this->result->body instanceof \Traversable);

        return $isArray ? new ArrayIterator($this->result->body) : new ArrayIterator([]);
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
     *
     * @return string
     */
    public function serialize()
    {
        throw new \LogicException(__METHOD__ . ' not supported');
    }

    /**
     * {@inheritdoc}
     *
     * @param string $serialized
     */
    public function unserialize($serialized) : void
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
