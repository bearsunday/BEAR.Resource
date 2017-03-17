<?php
/**
 * This file is part of the BEAR.Sunday package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

abstract class AbstractUri
{
    /**
     * @var string
     */
    public $scheme;

    /**
     * @var string
     */
    public $host;

    /**
     * @var string
     */
    public $path;

    /**
     * Associative query array
     *
     * @var array
     */
    public $query = [];

    /**
     * @var string
     */
    public $method;

    /**
     * @return string
     */
    public function __toString()
    {
        $uriWithQuery = "{$this->scheme}://{$this->host}{$this->path}" . ($this->query ? '?' . http_build_query($this->query) : '');

        return $uriWithQuery;
    }
}
