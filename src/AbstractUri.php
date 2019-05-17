<?php

declare(strict_types=1);

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
    public $method = 'get';

    /**
     * @return string
     */
    public function __toString()
    {
        return "{$this->scheme}://{$this->host}{$this->path}" . ($this->query ? '?' . http_build_query($this->query) : '');
    }
}
