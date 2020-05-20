<?php

declare(strict_types=1);

namespace BEAR\Resource;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Exception;
use IteratorAggregate;
use JsonSerializable;

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
     * @var null|string
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
     * @var RenderInterface
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
     * @param mixed $offset offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->body[$offset];
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
     * @param mixed $offset offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->body[$offset]);
    }

    /**
     * Set the value at the specified index
     *
     * @param mixed $offset offset
     */
    public function offsetUnset($offset) : void
    {
        unset($this->body[$offset]);
    }

    /**
     * Get the number of public properties in the ArrayObject
     *
     * @return int
     */
    public function count()
    {
        return count($this->body);
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
        $isTraversal = (is_array($this->body) || $this->body instanceof \Traversable);

        return $isTraversal ? new ArrayIterator($this->body) : new ArrayIterator([]);
    }

    /**
     * @Ray\Di\Di\Inject(optional=true)
     */
    public function setRenderer(RenderInterface $renderer) : void
    {
        $this->renderer = $renderer;
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        if (! $this->renderer instanceof RenderInterface) {
            $this->renderer = new JsonRenderer;
        }

        return $this->renderer->render($this);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize() : array
    {
        $body = $this->evaluate($this->body);
        if (! is_iterable($body)) {
            return ['value' => $body];
        }

        return $body;
    }

    /**
     * {@inheritdoc}
     */
    public function transfer(TransferInterface $responder, array $server) : void
    {
        $responder($this, $server);
    }

    /**
     * @param mixed $body
     *
     * @return array<string, mixed>|bool|int|string
     */
    private function evaluate($body)
    {
        if (is_array($body)) {
            /* @noinspection ForeachSourceInspection */
            foreach ($body as &$value) {
                if ($value instanceof RequestInterface) {
                    $result = $value();
                    $value = $result->body;
                }
            }
        }

        return $body;
    }
}
