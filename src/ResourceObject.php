<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use ArrayAccess;
use Countable;
use Exception;
use IteratorAggregate;
use JsonSerializable;

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
     * @var array
     */
    public $headers = [];

    /**
     * Resource representation
     *
     * @var string
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
     * @var \BEAR\Resource\RenderInterface
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
            $view = '';
            $msg = 'Exception caught in ' . get_class($this) . '::__toString() (log only)';
            error_log($msg . (string) $e);
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
    public function offsetUnset($offset)
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
    public function ksort()
    {
        if (! is_array($this->body)) {
            return;
        }
        ksort($this->body);
    }

    /**
     * Sort the entries by key
     */
    public function asort()
    {
        if (! is_array($this->body)) {
            return;
        }
        asort($this->body);
    }

    /**
     * Get array iterator
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        $isTraversal = (is_array($this->body) || $this->body instanceof \Traversable);

        return $isTraversal ? new \ArrayIterator($this->body) : new \ArrayIterator([]);
    }

    /**
     * Set renderer
     *
     * @param RenderInterface $renderer
     *
     * @return $this
     * @Ray\Di\Di\Inject(optional=true)
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
        if ($this->view !== null) {
            return $this->view;
        }
        if (! $this->renderer instanceof RenderInterface) {
            $this->renderer = new JsonRenderer;
        }

        return $this->renderer->render($this);
    }

    /**
     * @return mixed
     */
    public function jsonSerialize()
    {
        $body = $this->evaluate($this->body);
        $isTraversable = is_array($body) || $body instanceof \Traversable;
        if (! $isTraversable) {
            return ['value' => $body];
        }

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
