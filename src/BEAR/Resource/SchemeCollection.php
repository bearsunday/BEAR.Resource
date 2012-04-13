<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Adapter\Adapter;

/**
 * Resource scheme collection
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 */
class SchemeCollection implements \ArrayAccess
{
    use ArrayAccess;

    /**
     * SchemeCollection body
     *
     * @var array
     */
    private $body;

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
     * Temporary container
     *
     * @var string
     */
    private $container = array();

    /**
     * Set scheme
     *
     * @param string $scheme
     */
    public function scheme($scheme)
    {
        $this->scheme = $scheme;
        return $this;
    }

    /**
     * Set host
     *
     * @param string $host
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
     */
    public function toAdapter(Adapter $adapter)
    {
        $this->body[$this->scheme][$this->host] = $adapter;
        $this->scheme = $this->host = null;
        return $this;
    }
}
