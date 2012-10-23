<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Adapter\Adapter;
use ArrayObject;

/**
 * Resource scheme collection
 *
 * @package BEAR.Resource
 */
class SchemeCollection extends ArrayObject
{
    /**
     * Scheme
     *
     * @var string
     */
    private $scheme;

    /**
     * Host
     *
     * @var string
     */
    private $host;

    /**
     * Set scheme
     *
     * @param $scheme
     *
     * @return SchemeCollection
     */
    public function scheme($scheme)
    {
        $this->scheme = $scheme;

        return $this;
    }

    /**
     * Set host
     *
     * @param $host
     *
     * @return SchemeCollection
     */
    public function host($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Set resource adapter
     *
     * @param Adapter $adapter
     *
     * @return SchemeCollection
     */
    public function toAdapter(Adapter $adapter)
    {
        $this[$this->scheme][$this->host] = $adapter;
        $this->scheme = $this->host = null;

        return $this;
    }
}
