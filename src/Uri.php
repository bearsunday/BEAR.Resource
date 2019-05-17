<?php

declare(strict_types=1);

namespace BEAR\Resource;

use function array_key_exists;
use BEAR\Resource\Exception\UriException;

final class Uri extends AbstractUri
{
    /**
     * @throws \BEAR\Resource\Exception\UriException
     */
    public function __construct(string $uri, array $query = [])
    {
        $this->validate($uri);
        if (count($query) !== 0) {
            $uri = uri_template($uri, $query);
        }
        $parsedUrl = (array) parse_url($uri);
        list($this->scheme, $this->host, $this->path) = array_values($parsedUrl);
        if (array_key_exists('query', $parsedUrl)) {
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
        if (filter_var($uri, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            return;
        }

        throw new UriException($uri, 500);
    }
}
