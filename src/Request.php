<?php
/**
 * BEAR.Resource
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * Interface for resource adapter provider.
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 *
 * @Scope("prototype")
 */
class Request
{
    /**
     * URI
     *
     * @var string
     */
    public $uri;

    /**
     * Resource Objcet
     *
     * @var BEAR\Resource\Object
     */
    public $ro;

    /**
     * @var string
     */
    public $method = '';

    /**
     * @var array
     */
    public $query = array();

    /**
     * @var array
     */
    public $options = array();

    /**
     * Request option (eager or lazy)
     *
     * @var string
     */
    public $in;

    /**
     *
     * @return string
     */
    public function __toString()
    {
        $query = http_build_query($this->query, '&');
        return "{$this->method} {$this->ro->uri}" . ($query ? '?' :  '') . $query;
    }
}