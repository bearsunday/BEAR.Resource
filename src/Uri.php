<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

final class Uri
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

    /**
     * @param string $uri
     * @param array  $query
     */
    public function __construct($uri, array $query = [])
    {
        $this->uri = $uri;
        $this->query = $query;
    }

    /**
     * Return URI string
     *
     * @return string
     */
    public function __toString()
    {
        $uriWithQuery = $this->uri . ($this->query ? '?' . http_build_query($this->query) : '');

        return $uriWithQuery;
    }
}
