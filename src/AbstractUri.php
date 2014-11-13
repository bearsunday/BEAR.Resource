<?php
/**
 * This file is part of the _package_ package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
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
     * @var string
     */
    public $fullPath;
    /**
     * Associative query array
     *
     * @var array
     */
    public $query = [];

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $uriWithQuery = "{$this->scheme}://{$this->host}{$this->path}" . ($this->query ? '?' . http_build_query($this->query) : '');

        return $uriWithQuery;
    }
}
