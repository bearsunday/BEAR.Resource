<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Adapter\App;

use BEAR\Resource\Object as ResourceObject;

/**
 * The value of a link constant
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 */
class Link
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