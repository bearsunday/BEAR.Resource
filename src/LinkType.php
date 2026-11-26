<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\UriException;

use function parse_str;
use function parse_url;
use function preg_match;
use function preg_match_all;

use const PHP_URL_FRAGMENT;
use const PREG_SET_ORDER;
use const PREG_UNMATCHED_AS_NULL;

/** @psalm-import-type Query from Types */
final class LinkType
{
    public const SELF_LINK = 'self';
    public const NEW_LINK = 'new';
    public const CRAWL_LINK = 'crawl';

    /**
     * @param Query $query Overlaid on the body to expand the href, and on the expanded query to request the linked resource
     *
     * @psalm-pure
     */
    public function __construct(
        public string $key,
        public string $type,
        public array $query = [],
    ) {
    }

    /**
     * The links of a URI's `#link=type:rel[?query](,type:rel[?query])*` fragment, in order; none without a fragment
     *
     * A link query is form-urlencoded as http_build_query() writes it, which is how Request::toUri() writes it.
     *
     * @return list<self>
     *
     * @throws UriException
     */
    public static function listFromUri(string $uri): array
    {
        $fragment = parse_url($uri, PHP_URL_FRAGMENT);
        if ($fragment === false) {
            throw new UriException($uri, 500);
        }

        if ($fragment === null || $fragment === '') {
            return [];
        }

        $link = '(self|new|crawl):([A-Za-z0-9_-]+)(?:\?([A-Za-z0-9_.~%+=&-]+))?';
        if (preg_match("/\\Alink={$link}(,{$link})*\\z/", $fragment) !== 1) {
            throw new UriException($uri, 500);
        }

        preg_match_all("/{$link}/", $fragment, $matches, PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL);
        $links = [];
        /** @var array{0: string, 1: string, 2: string, 3: string|null} $match */
        foreach ($matches as $match) {
            [, $type, $rel, $linkQuery] = $match;
            $query = [];
            if ($linkQuery !== null) {
                if ($type === self::CRAWL_LINK) {
                    throw new UriException("{$uri}: a crawl link takes no query", 500);
                }

                parse_str($linkQuery, $query);
            }

            /** @psalm-suppress MixedArgumentTypeCoercion parse_str() outputs array<int|string, mixed> but Query is array<string, mixed> */
            $links[] = new self($rel, $type, $query);
        }

        return $links;
    }
}
