<?php

declare(strict_types=1);

namespace BEAR\Resource;

use ArrayAccess;
use ArrayIterator;
use BEAR\Resource\Exception\IlligalAccessException;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Ray\Di\Di\Inject;
use ReturnTypeWillChange;
use Stringable;
use Throwable;

use function asort;
use function assert;
use function count;
use function is_array;
use function is_iterable;
use function is_string;
use function ksort;
use function sprintf;
use function trigger_error;

use const E_USER_WARNING;

/**
 * @phpstan-implements ArrayAccess<string, mixed>
 * @phpstan-implements IteratorAggregate<(int|string), mixed>
 */
abstract class ResourceObject implements AcceptTransferInterface, ArrayAccess, Countable, IteratorAggregate, JsonSerializable, Stringable
{
    /**
     * Uri
     *
     * @var AbstractUri
     * @psalm-suppress PropertyNotSetInConstructor
     */
    public $uri;

    /**
     * Status code
     *
     * @var int
     */
    public $code = 200;

    /**
     * Resource header
     *
     * @var array<string, string>
     */
    public $headers = [];

    /**
     * Resource representation
     *
     * @var ?string
     */
    public $view;

    /**
     * Body
     *
     * @var mixed
     */
    public $body;

    /**
     * Renderer
     *
     * @var ?RenderInterface
     */
    protected $renderer;

    /**
     * Return representational string
     *
     * Return object hash if representation renderer is not set.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $view = $this->toString();
        } catch (Throwable $e) {
            $msg = sprintf("%s(%s)\n%s", $e::class, $e->getMessage(), $e->getTraceAsString());
            trigger_error($msg, E_USER_WARNING);

            return '';
        }

        return $view;
    }

    /** @return list<string> */
    public function __sleep()
    {
        if (is_array($this->body)) {
            /** @psalm-suppress MixedAssignment */
            foreach ($this->body as &$item) {
                if (! ($item instanceof RequestInterface)) {
                    continue;
                }

                $item = ($item)();
            }
        }

        return ['uri', 'code', 'headers', 'body', 'view'];
    }

    /**
     * Returns the body value at the specified index
     *
     * @param int|string $offset offset
     *
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (is_array($this->body)) {
            return $this->body[$offset];
        }

        throw new IlligalAccessException((string) $offset);
    }

    /**
     * Sets the body value at the specified index to renew
     *
     * @param array-key $offset offset
     * @param mixed     $value  value
     *
     * @return void
     */
    #[ReturnTypeWillChange]
    public function offsetSet($offset, mixed $value)
    {
        if ($this->body === null) {
            $this->body = [];
        }

        if (! is_array($this->body)) {
            throw new IlligalAccessException((string) $offset);
        }

        $this->body[$offset] = $value;
    }

    /**
     * Returns whether the requested index in body exists
     *
     * @param array-key $offset offset
     *
     * @return bool
     */
    #[ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        if (is_array($this->body)) {
            return isset($this->body[$offset]);
        }

        return false;
    }

    /**
     * Set the value at the specified index
     *
     * @param int|string $offset offset
     */
    #[ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        assert(is_array($this->body));
        unset($this->body[$offset]);
    }

    /**
     * Get the number of public properties in the ArrayObject
     */
    public function count(): int
    {
        if (is_countable($this->body)) {
            return count($this->body);
        }

        throw new IlligalAccessException();
    }

    /**
     * Sort the entries by key
     */
    public function ksort(): void
    {
        if (! is_array($this->body)) {
            return;
        }

        ksort($this->body);
    }

    /**
     * Sort the entries by key
     */
    public function asort(): void
    {
        if (! is_array($this->body)) {
            return;
        }

        asort($this->body);
    }

    /**
     * @return ArrayIterator<int|string, mixed>
     * @psalm-return ArrayIterator<empty, empty>|ArrayIterator<int|string, mixed>
     * @phpstan-return ArrayIterator<int|string, mixed>
     */
    public function getIterator(): ArrayIterator
    {
        return is_array($this->body) ? new ArrayIterator($this->body) : new ArrayIterator([]);
    }

    /**
     * @return self
     *
     * @Inject(optional=true)
     */
    #[Inject(optional: true)]
    public function setRenderer(RenderInterface $renderer)
    {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        if (is_string($this->view)) {
            return $this->view;
        }

        if (! $this->renderer instanceof RenderInterface) {
            $this->renderer = new JsonRenderer();
        }

        return $this->renderer->render($this);
    }

    /** @return array<int|string, mixed> */
    public function jsonSerialize(): array
    {
        /** @psalm-suppress MixedAssignment */
        if (! is_iterable($this->body)) {
            return ['value' => $this->body];
        }

        assert(is_array($this->body));

        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function transfer(TransferInterface $responder, array $server)
    {
        $responder($this, $server);
    }

    public function __clone()
    {
        $this->uri = clone $this->uri;
    }
}
