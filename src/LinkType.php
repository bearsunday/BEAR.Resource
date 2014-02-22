<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
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

    /**
     * @param $type
     * @param string $key
     */
    public function __construct($key, $type)
    {
        $this->key = $key;
        $this->type = $type;
    }
}
