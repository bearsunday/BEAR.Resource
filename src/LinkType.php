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

    /**
     * Link key
     *
     * @var string
     */
    public $key;

    /**
     * Link type
     *
     * @var string
     */
    public $type;

    public function __construct(string $key, string $type)
    {
        $this->key = $key;
        $this->type = $type;
    }
}
