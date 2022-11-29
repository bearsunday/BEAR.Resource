<?php

declare(strict_types=1);

namespace BEAR\Resource;

final class LinkType
{
    public const SELF_LINK = 'self';
    public const NEW_LINK = 'new';
    public const CRAWL_LINK = 'crawl';

    public function __construct(
        public string $key,
        public string $type,
    ) {
    }
}
