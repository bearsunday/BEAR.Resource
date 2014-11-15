<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\Method;
use BEAR\Resource\Exception\OutOfBounds;

final class Request extends AbstractRequest
{
    const GET = 'get';
    const POST = 'post';
    const PUT = 'put';
    const PATCH = 'patch';
    const DELETE = 'delete';
    const HEAD = 'head';
    const OPTIONS = 'options';

    const LAZY = 'lazy';

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
    public $ro;

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
     * @var LinkType[]
     */
    public $links = [];

    /**
     * Request Result
     *
     * @var Object
     */
    private $result;

    /**
     * @var InvokerInterface
     */
    private $invoker;

    /**
     * @param InvokerInterface $invoker
     * @param ResourceObject   $ro
     * @param string           $method
     * @param array            $query
     * @param LinkType[]       $links
     */
    public function __construct(
        InvokerInterface $invoker,
        ResourceObject $ro,
        $method = self::GET,
        array $query = [],
        array $links = []
    ) {
        $this->invoker = $invoker;
        $this->ro = $ro;
        if (! in_array($method, [self::GET, self::POST, self::PUT, self::PATCH, self::DELETE, self::HEAD, self::OPTIONS])) {
            throw new Method($method);
        }
        $this->method = $method;
        $this->query = $query;
        $this->links = $links;
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery(array $query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addQuery(array $query)
    {
        $this->query = array_merge($this->query, $query);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function toUriWithMethod()
    {
        $uri = $this->toUri();

        return "{$this->method} {$uri}";
    }

    /**
     * {@inheritDoc}
     */
    public function toUri()
    {
        $this->ro->uri->query = $this->query;

        return (string) $this->ro->uri;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $this->invoke();

        return (string) $this->result;
    }

    /**
     * @return mixed
     */
    private function invoke()
    {
        if (is_null($this->result)) {
            $this->result = $this->__invoke();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(array $query = null)
    {
        if (!is_null($query)) {
            $this->query = array_merge($this->query, $query);
        }
        $result = $this->invoker->invoke($this);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        $this->invoke();
        if (!isset($this->result->body[$offset])) {
            throw new OutOfBounds("[$offset] for object[" . get_class($this->result) . "]");
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
        return md5(get_class($this->ro) . $this->method . serialize($this->query) . serialize($this->links));
    }
}
