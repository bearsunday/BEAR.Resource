<?php

declare(strict_types=1);

namespace BEAR\Resource;

use ArrayAccess;
use ArrayIterator;
use BEAR\Resource\Exception\OutOfBoundsException;
use IteratorAggregate;
use JsonSerializable;
use LogicException;
use Override;
use ReturnTypeWillChange;
use Serializable;
use Stringable;
use Throwable;

use function array_key_exists;
use function array_merge;
use function assert;
use function is_array;
use function md5;
use function serialize;
use function trigger_error;

use const E_USER_ERROR;
use const PHP_EOL;

/**
 * @property int     $code
 * @property Headers $headers
 * @property mixed   $body
 * @property string  $view
 * @phpstan-implements IteratorAggregate<string, mixed>
 * @phpstan-implements ArrayAccess<string, mixed>
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-import-type Query from Types
 * @psalm-import-type Headers from Types
 * @psalm-import-type Body from Types
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
     */
    public Method $method = Method::GET;

    /**
     * Options
     *
     * @var Body
     */
    public $options = [];

    /**
     * Request option (eager or lazy)
     *
     * @var 'eager'|'lazy'
     */
    public $in = 'lazy';

    /**
     * Request Result, memoized by the first read; reset whenever the query or links change
     *
     * @var ?ResourceObject
     */
    protected $result;

    /**
     * @param Query          $query
     * @param list<LinkType> $links
     */
    public function __construct(
        protected InvokerInterface $invoker,
        public ResourceObject $resourceObject,
        Method $method = Method::GET,
        public array $query = [],
        // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
        public array $links = [],
        private readonly LinkerInterface|null $linker = null,
    ) {
        $this->method = $method;
    }

    /** @psalm-suppress UnevaluatedCode */
    #[Override]
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
     * {@inheritDoc}
     *
     * @param Query $query
     */
    #[Override]
    public function __invoke(array|null $query = null): ResourceObject
    {
        if (is_array($query)) {
            $this->query = array_merge($this->query, $query);
            $this->result = null;
        }

        $this->resourceObject->uri->query = $this->query;
        if ($this->links && $this->linker instanceof LinkerInterface) {
            return $this->linker->invoke($this);
        }

        return clone $this->invoker->invoke($this);
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     *
     * @throws OutOfBoundsException
     */
    #[Override]
    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        throw new OutOfBoundsException(__METHOD__ . ' is unavailable.', 400);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $offset
     *
     * @return never
     *
     * @throws OutOfBoundsException
     */
    #[Override]
    #[ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($offset);

        throw new OutOfBoundsException(__METHOD__ . ' is unavailable.', 400);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function request()
    {
        if ($this->in === 'eager') {
            $this->result = $this->invoke();

            return $this->result;
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $offset
     *
     * @return mixed
     *
     * @throws OutOfBoundsException
     */
    #[Override]
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        $this->invoke();
        assert($this->result instanceof ResourceObject);
        if (! is_array($this->result->body) || ! array_key_exists($offset, $this->result->body)) {
            throw new OutOfBoundsException("[{$offset}] for object[" . $this->result::class . ']', 400);
        }

        return $this->result->body[$offset];
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
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
    #[Override]
    public function getIterator(): ArrayIterator
    {
        $this->invoke();
        assert($this->result instanceof ResourceObject);

        return is_array($this->result->body) ? new ArrayIterator($this->result->body) : new ArrayIterator([]);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function hash(): string
    {
        return md5($this->resourceObject::class . $this->method->value . serialize($this->query) . serialize($this->links));
    }

    /**
     * {@inheritDoc}
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
     * @param Body $data
     *
     * @codeCoverageIgnore
     */
    public function __unserialize(array $data): void
    {
        unset($data);
    }

    /** Lazy evaluation with memoization - delegates to __invoke() on first call */
    private function invoke(): ResourceObject
    {
        if ($this->result === null) {
            /* @noinspection ImplicitMagicMethodCallInspection */
            $this->result = ($this)();
        }

        return $this->result;
    }

    #[Override]
    public function jsonSerialize(): mixed
    {
        return $this->invoke();
    }

    /**
     * @return never
     *
     * @codeCoverageIgnore
     * @psalm-suppress MethodSignatureMustProvideReturnType - method not supported
     */
    #[Override]
    public function serialize()
    {
        $this->__serialize();
    }

    /**
     * @param string $data
     *
     * @codeCoverageIgnore
     * @psalm-suppress MethodSignatureMustProvideReturnType - method not supported
     */
    #[Override]
    public function unserialize($data)
    {
        unset($data);
    }
}
