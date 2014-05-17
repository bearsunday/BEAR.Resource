<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Exception;
use ArrayIterator;
use Traversable;
use JsonSerializable;
use Ray\Di\Di\Inject;

abstract class ResourceObject implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * URI
     *
     * @var string
     */
    public $uri = '';

    /**
     * Resource status code
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
     * Resource links
     *
     * @var array
     */
    public $links = [];

    /**
     * Renderer
     *
     * @var \BEAR\Resource\RenderInterface
     */
    protected $renderer;

    /**
     * Body
     *
     * @var array
     */
    public $body;

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
     *
     * @return void
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
     *
     * @return bool
     */
    public function ksort()
    {
        $isTraversal = (is_array($this->body) || $this->body instanceof \Traversable);
        if (! $isTraversal) {
            return $this->body;
        }
        $body = (array) $this->body;

        return ksort($body);
    }

    /**
     * Sort the entries by key
     *
     * @return bool
     */
    public function asort()
    {
        $isTraversal = (is_array($this->body) || $this->body instanceof \Traversable);
        if (! $isTraversal) {
            return $this->body;
        }
        $body = (array) $this->body;

        return asort($body);
    }

    /**
     * Get array iterator
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        $isTraversal = (is_array($this->body) || $this->body instanceof \Traversable);

        return ($isTraversal ? new \ArrayIterator($this->body) : new \ArrayIterator([]));
    }

    /**
     * Set renderer
     *
     * @param RenderInterface $renderer
     *
     * @return $this
     * @Ray\Di\Di\Inject(optional = true)
     */
    public function setRenderer(RenderInterface $renderer)
    {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * Return representational string
     *
     * Return object hash if representation renderer is not set.
     *
     * @return string
     */
    public function __toString()
    {
        /** @var $this ResourceObject */
        if (is_string($this->view)) {
            return $this->view;
        }
        if ($this->renderer instanceof RenderInterface) {
            try {
                $view = $this->renderer->render($this);
            } catch (Exception $e) {
                $view = '';
                error_log('Exception caught in ' . __METHOD__);
                error_log((string) $e);
            }

            return $view;
        }
        if (is_scalar($this->body)) {
            return (string) $this->body;
        }
        error_log('No renderer bound for \BEAR\Resource\RenderInterface' . get_class($this) . ' in ' . __METHOD__);

        return '';
    }

    public function jsonSerialize()
    {
        $body = $this->body;
        $isTraversable = is_array($body) || $body instanceof \Traversable;
        if (! $isTraversable) {
            return ['value' => $this->body];
        }
        foreach ($body as &$value) {
            if ($value instanceof RequestInterface) {
                $result = $value();
                $value = $result->body;
            }
        }

        return $body;
    }
}
