<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

final class LinkType
{
    /**
     * Self link
     *
     * @var string
     */
    const SELF_LINK = 'self';

    /**
     * New link
     *
     * @var string
     */
    const NEW_LINK = 'new';

    /**
     * Crawl link
     *
     * @var string
     */
    const CRAWL_LINK = 'crawl';

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
