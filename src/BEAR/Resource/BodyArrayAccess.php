<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use ArrayIterator;
use Traversable;

/**
 * Trait for array access
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 */
trait BodyArrayAccess
{
    /**
     * Returns the body value at the specified index
     *
     * @param mixed $offset offset
     *
     * @return mixed
     * @ignore
     */
    public function offsetGet($offset)
    {
        return $this->body[$offset];
    }

    /**
     * Sets the body value at the specified index to newval
     *
     * @param mixed $offset offset
     * @param mixed $value  value
     *
     * @return void
     * @ignore
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
     * @ignore
     */
    public function offsetExists($offset)
    {
        return isset($this->body[$offset]);
    }

    /**
     * nsets the value at the specified index
     *
     * @param mixed $offset offset
     *
     * @return void
     * @ignore
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
     * @ignore
     */
    public function ksort()
    {
        return ksort($this->body);
    }

    /**
     * Sort the entries by key
     *
     * @return bool
     * @ignore
     */
    public function asort()
    {
        return asort($this->body);
    }

    /**
     * Get array iterator
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return ((is_array($this->body) || $this->body instanceof Traversable)
        ?  new ArrayIterator($this->body)
        :  new ArrayIterator([]));
    }
}
