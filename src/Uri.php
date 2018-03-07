<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\UriException;

final class Uri extends AbstractUri
{
    /**
     * @param string $uri
     * @param array  $query
     *
     * @throws \BEAR\Resource\Exception\UriException
     */
    public function __construct(string $uri, array $query = [])
    {
        $this->validate($uri);
        if (count($query) !== 0) {
            $uri = uri_template($uri, $query);
        }
        $parsedUrl = parse_url($uri);
        list($this->scheme, $this->host, $this->path) = array_values($parsedUrl);
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $this->query);
        }
        if (count($query) !== 0) {
            $this->query = $query + $this->query;
        }
    }

    /**
     * @throws UriException
     */
    private function validate(string $uri)
    {
        if (! filter_var($uri, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            $msg = is_string($uri) ? $uri : gettype($uri);
            throw new UriException($msg, 500);
        }
    }
}
