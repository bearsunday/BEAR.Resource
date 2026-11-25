<?php

declare(strict_types=1);

namespace BEAR\Resource;

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
}
