<?php
/**
 * BEAR.Resource
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * Link type DTO
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 */
class LinkType
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
}
