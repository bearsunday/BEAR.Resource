<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Object as ResourceObject;

/**
 * URI
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 */
class Uri
{
    /**
     * URI ($schema://$host/$path)
     *
     * @var string
     */
    public $uri;

    /**
     * Query
     *
     * @var array
     */
    public $query;

    public function __construct($uri, array $query)
    {
        $this->uri = $uri;
        $this->query = $query;
    }

    /**
     * Return URI string.
     *
     * @return string
     */
    public function __toString()
    {
        $uriWithQuery = $this->uri . ($this->query ? '?' . http_build_query($this->query) : '');
        return $uriWithQuery;
    }
}