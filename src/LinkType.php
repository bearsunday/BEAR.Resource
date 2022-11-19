<?php

declare(strict_types=1);

namespace BEAR\Resource;

final class LinkType
{
    /**
     * Self link
     */
    public const SELF_LINK = 'self';

    /**
     * New link
     */
    public const NEW_LINK = 'new';

    /**
     * Crawl link
     */
    public const CRAWL_LINK = 'crawl';

    public function __construct(
        /**
         * Link key
         */
        public string $key,
        /**
         * Link type
         */
        public string $type
    )
    {
    }
}
