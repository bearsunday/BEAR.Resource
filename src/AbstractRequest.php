<?php

declare(strict_types=1);

namespace BEAR\Resource;

use ArrayAccess;
use ArrayIterator;
use BEAR\Resource\Exception\MethodException;
use BEAR\Resource\Exception\OutOfBoundsException;
use IteratorAggregate;
use JsonSerializable;
use LogicException;
use ReturnTypeWillChange;
use Serializable;
use Stringable;
use Throwable;

use function array_key_exists;
use function array_merge;
use function assert;
use function get_class;
use function in_array;
use function is_array;
use function md5;
use function serialize;
use function strtolower;
use function trigger_error;

use const E_USER_ERROR;
use const PHP_EOL;

/**
 * @property int    $code
 * @property array  $headers
 * @property mixed  $body
 * @property string $view
 * @phpstan-implements IteratorAggregate<string, mixed>
 * @phpstan-implements ArrayAccess<string, mixed>
 * @psalm-suppress PropertyNotSetInConstructor
 */
abstract class AbstractRequest implements RequestInterface, ArrayAccess, IteratorAggregate, Serializable, JsonSerializable, Stringable
{
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
     * @var 'eager'|'lazy'
     */
    public $in = 'lazy'; // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint

    /**
     * Links
     *
     * @var LinkType[]
     */
    public $links = [];

    /** @var ResourceObject */
    public $resourceObject;

    /**
     * Request Result
     *
     * @var ?ResourceObject
     */
    protected $result;

    /** @var InvokerInterface */
    protected $invoker;

    private ?\BEAR\Resource\LinkerInterface $linker;

    /**
     * @param array<string, mixed> $query
     * @param list<LinkType>       $links
     *
     * @throws MethodException
     */
    public function __construct(
        InvokerInterface $invoker,
        ResourceObject $ro,
        string $method = Request::GET,
        array $query = [],
        array $links = [],
        ?LinkerInterface $linker = null
    ) {
        $this->invoker = $invoker;
        $this->resourceObject = $ro;
        if (! in_array(strtolower($method), ['get', 'post', 'put', 'patch', 'delete', 'head', 'options'], true)) {
            throw new MethodException($method, 400);
        }

        $this->method = $method;
        $this->query = $query;
        $this->links = $links;
        $this->linker = $linker;
    }

    /**
     * @psalm-suppress UnevaluatedCode
     */
    public function __toString(): string
    {
        try {
            $this->invoke();

            return (string) $this->result;
        } catch (Throwable $e) {
            trigger_error($e->getMessage() . PHP_EOL . $e->getTraceAsString(), E_USER_ERROR);

            /** @noinspection PhpUnreachableStatementInspection */
            return ''; // @phpstan-ignore-line
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param array<string, mixed> $query
     */
    public function __invoke(?array $query = null): ResourceObject
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
     * @return mixed
     *
     * @noinspection MagicMethodsValidityInspection
     */
    public function __get(string $name)
    {
        $this->result = $this->invoke();

        return $this->result->{$name};
    }

    /**
     * {@inheritdoc}
     *
     * @throws OutOfBoundsException
     */
    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        throw new OutOfBoundsException(__METHOD__ . ' is unavailable.', 400);
    }

    /**
     * {@inheritdoc}
     *
     * @throws OutOfBoundsException
     */
    #[ReturnTypeWillChange]
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
     * @param string $offset
     *
     * @return mixed
     *
     * @throws OutOfBoundsException
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        $this->invoke();
        assert($this->result instanceof ResourceObject);
        if (! is_array($this->result->body) || ! isset($this->result->body[$offset])) {
            throw new OutOfBoundsException("[{$offset}] for object[" . get_class($this->result) . ']', 400);
        }

        return $this->result->body[$offset];
    }

    /**
     * {@inheritdoc}
     */
    #[ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        $this->invoke();
        assert($this->result instanceof ResourceObject);

        return is_array($this->result->body) && array_key_exists($offset, $this->result->body);
    }

    /**
     * Invoke resource request then return resource body iterator
     *
     * @psalm-return ArrayIterator
     * @phpstan-return ArrayIterator<string, mixed>
     */
    public function getIterator(): ArrayIterator
    {
        $this->invoke();
        assert($this->result instanceof ResourceObject);

        return is_array($this->result->body) ? new ArrayIterator($this->result->body) : new ArrayIterator([]);
    }

    /**
     * {@inheritdoc}
     */
    public function hash(): string
    {
        return md5(get_class($this->resourceObject) . $this->method . serialize($this->query) . serialize($this->links));
    }

    /**
     * {@inheritdoc}
     *
     * @return never
     *
     * @noinspection MagicMethodsValidityInspection
     */
    public function __serialize()
    {
        throw new LogicException(__METHOD__ . ' not supported');
    }

    /**
     * @param array<mixed> $data
     *
     * @codeCoverageIgnore
     */
    public function __unserialize(array $data): void
    {
        unset($data);
    }

    private function invoke(): ResourceObject
    {
        if ($this->result === null) {
            /* @noinspection ImplicitMagicMethodCallInspection */
            $this->result = ($this)();
        }

        return $this->result;
    }

    public function jsonSerialize(): ResourceObject
    {
        return $this->invoke();
    }

    /**
     * @return never
     * @psalm-return never-returns
     *
     * @codeCoverageIgnore
     */
    public function serialize()
    {
        $this->__serialize();
    }

    /**
     * @param string $data
     *
     * @codeCoverageIgnore
     */
    public function unserialize($data)
    {
        unset($data);
    }
}
