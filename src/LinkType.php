<?php

declare(strict_types=1);

namespace BEAR\Resource;

final class LinkType
{
    /**
     * Self link
     *
     * @var string
     */
    public const SELF_LINK = 'self';

    /**
     * New link
     *
     * @var string
     */
    public const NEW_LINK = 'new';

    /**
     * Crawl link
     *
     * @var string
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
