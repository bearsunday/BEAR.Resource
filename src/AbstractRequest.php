<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\LogicException;

abstract class AbstractRequest implements RequestInterface, \ArrayAccess, \IteratorAggregate
{
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
     * @var \BEAR\Resource\LinkType[]
     */
    public $links = [];

    /**
     * @param mixed $offset
     * @param mixed $value
     *
     * @throws Exception\LogicException
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function offsetSet($offset, $value)
    {
        throw new LogicException(__METHOD__ . ' is unavailable.');
    }

    /**
     * @param mixed $offset
     *
     * @throws Exception\LogicException
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function offsetUnset($offset)
    {
        throw new LogicException(__METHOD__ . ' is unavailable.');
    }
}
