<?php

declare(strict_types=1);

namespace BEAR\Resource;

use ArrayAccess;
use ArrayIterator;
use BEAR\Resource\Exception\IlligalAccessException;
use Countable;
use Exception;
use function is_string;
use IteratorAggregate;
use JsonSerializable;
use Ray\Di\Di\Inject;

/**
 * @phpstan-implements \ArrayAccess<string, mixed>
 * @phpstan-implements \IteratorAggregate<int|string, mixed>
 */
abstract class ResourceObject implements AcceptTransferInterface, ArrayAccess, Countable, IteratorAggregate, JsonSerializable, ToStringInterface
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
     * @var array<int|string, mixed>|mixed
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
        if (is_string($this->view)) {
            return $this->view;
        }
        try {
            $view = $this->toString();
        } catch (Exception $e) {
            $msg = sprintf("%s(%s)\n%s", get_class($e), $e->getMessage(), $e->getTraceAsString());
            trigger_error($msg, E_USER_WARNING);

            return '';
        }

        return $view;
    }

    public function __sleep()
    {
        if (is_array($this->body)) {
            /** @psalm-suppress MixedAssignment */
            foreach ($this->body as &$item) {
                if ($item instanceof RequestInterface) {
                    $item = ($item)();
                }
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
     * @param mixed $offset offset
     * @param mixed $value  value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->body[$offset] = $value;
    }

    /**
     * Returns whether the requested index in body exists
     *
     * @param int|string $offset offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        if (is_array($this->body)) {
            return isset($this->body[$offset]);
        }

        throw new IlligalAccessException((string) $offset);
    }

    /**
     * Set the value at the specified index
     *
     * @param int|string $offset offset
     */
    public function offsetUnset($offset) : void
    {
        if (is_array($this->body)) {
            unset($this->body[$offset]);
        }
    }

    /**
     * Get the number of public properties in the ArrayObject
     *
     * @return int
     */
    public function count()
    {
        if ($this->body instanceof Countable || is_array($this->body)) {
            return count($this->body);
        }

        throw new IlligalAccessException();
    }

    /**
     * Sort the entries by key
     */
    public function ksort() : void
    {
        if (! is_array($this->body)) {
            return;
        }
        ksort($this->body);
    }

    /**
     * Sort the entries by key
     */
    public function asort() : void
    {
        if (! is_array($this->body)) {
            return;
        }
        asort($this->body);
    }

    /**
     * @return ArrayIterator<int|string, mixed>
     *
     * @phpstan-return ArrayIterator<int|string, mixed>
     * @psalm-return ArrayIterator<empty, empty>|ArrayIterator<int|string, mixed>
     */
    public function getIterator() : ArrayIterator
    {
        return is_array($this->body) ? new ArrayIterator((array) $this->body) : new ArrayIterator([]);
    }

    /**
     * @Inject(optional=true)
     *
     * @return self
     */
    public function setRenderer(RenderInterface $renderer)
    {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        if (is_string($this->view)) {
            return $this->view;
        }
        if (! $this->renderer instanceof RenderInterface) {
            $this->renderer = new JsonRenderer;
        }

        return $this->renderer->render($this);
    }

    /**
     * @return array<int|string, mixed>
     */
    public function jsonSerialize() : array
    {
        /** @psalm-suppress MixedAssignment */
        if (! is_iterable($this->body)) {
            return ['value' => $this->body];
        }
        $body = $this->evaluate($this->body);
        assert(is_array($body));

        return $body;
    }

    /**
     * {@inheritdoc}
     */
    public function transfer(TransferInterface $responder, array $server)
    {
        $responder($this, $server);
    }

    /**
     * @param mixed $body
     *
     * @return mixed
     */
    private function evaluate($body)
    {
        /* @noinspection ForeachSourceInspection */
        /** @psalm-suppress MixedAssignment */
        foreach ($body as &$value) {
            if ($value instanceof RequestInterface) {
                $result = $value();
                $value = $result->body;
            }
        }

        return $body;
    }
}
