<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\UriException;

use function array_key_exists;
use function count;
use function filter_var;
use function parse_str;
use function parse_url;
use function sprintf;

use const FILTER_FLAG_PATH_REQUIRED;
use const FILTER_VALIDATE_URL;

final class Uri extends AbstractUri
{
    /**
     * @param array<string, mixed> $query
     *
     * @throws UriException
     */
    public function __construct(string $uri, array $query = [])
    {
        $this->validate($uri);
        if (count($query) !== 0) {
            $uri = uri_template($uri, $query);
        }

        $parts = (array) parse_url($uri);
        $host = isset($parts['port']) ? sprintf('%s:%s', $parts['host'] ?? '', $parts['port'] ?? '') : $parts['host'] ?? '';
        [$this->scheme, $this->host, $this->path] = [$parts['scheme'] ?? '', $host, $parts['path'] ?? ''];
        $parseQuery = $this->query;
        if (array_key_exists('query', $parts)) {
            parse_str($parts['query'], $parseQuery);
            /** @var array<string, mixed> $parseQuery */
            $this->query = $parseQuery;
        }

        if (count($query) !== 0) {
            $this->query = $query + $parseQuery;
        }
    }

    /**
     * @throws UriException
     */
    private function validate(string $uri): void
    {
        if (filter_var($uri, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            return;
        }

        throw new UriException($uri, 500);
    }
}
