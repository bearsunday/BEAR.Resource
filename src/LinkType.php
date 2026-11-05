<?php

declare(strict_types=1);

namespace BEAR\Resource;

/** @psalm-immutable */
final class LinkType
{
    public const SELF_LINK = 'self';
    public const NEW_LINK = 'new';
    public const CRAWL_LINK = 'crawl';

    /** @psalm-pure */
    public function __construct(
        public string $key,
        public string $type,
    ) {
    }
}
