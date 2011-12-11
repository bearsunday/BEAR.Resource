<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * Resource scheme collection
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 */
class SchemeCollection implements \ArrayAccess
{
    use ArrayAccess;

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
     * @param string $scheme
     */
    public function host($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Set resource adapter
     *
     * @param string $scheme
     */
    public function toAdapter($adapter)
    {
        $this->body[$this->scheme][$this->host] = $adapter;
        $this->scheme = $this->host = null;
        return $this;
    }

    public function __toString()
    {
        $body = &$this->body;
        array_walk_recursive($body, function($value) {
            return is_object($value) ? get_class($value) : $value;
        });
        return print_r( $this->body, true);
    }
}
