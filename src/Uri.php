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
use function uri_template;

use const FILTER_FLAG_PATH_REQUIRED;
use const FILTER_VALIDATE_URL;

/** @psalm-import-type Query from Types */
final class Uri extends AbstractUri
{
    /**
     * @param Query $query Query parameters that may contain user input
     *
     * @throws UriException
     *
     * @psalm-taint-source input $query
     *
     * @psalm-impure
     */
    public function __construct(
        string $uri,
        array $query = [],
    ) {
        $this->validate($uri);
        if (count($query) !== 0) {
            $uri = uri_template($uri, $query);
        }

        $parts = (array) parse_url($uri);
        // parse_url() returns array<string>|false but we cast to array
        $host = isset($parts['port']) ? sprintf('%s:%s', $parts['host'] ?? '', $parts['port'] ?? '') : $parts['host'] ?? ''; // @phpstan-ignore-line
        [$this->scheme, $this->host, $this->path] = [$parts['scheme'] ?? '', $host, $parts['path'] ?? ''];
        $parseQuery = $this->query;
        if (array_key_exists('query', $parts)) {
            parse_str($parts['query'], $parseQuery);
            // parse_str() outputs array<int|string, array|string> but Query is array<string, mixed>
            /** @psalm-suppress MixedPropertyTypeCoercion */
            $this->query = $parseQuery; // @phpstan-ignore-line
        }

        if (count($query) === 0) {
            return;
        }

        // Array union may contain int keys from parse_str output
        /** @psalm-suppress MixedPropertyTypeCoercion */
        $this->query = $query + $parseQuery; // @phpstan-ignore-line
    }

    /**
     * @throws UriException
     *
     * @psalm-pure
     */
    private function validate(string $uri): void
    {
        if (filter_var($uri, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            return;
        }

        throw new UriException($uri, 500);
    }
}
