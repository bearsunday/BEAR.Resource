<?php
/**
 * BEAR.Resource
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Object as ResourceObject;

/**
 * The value of a link constant
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 */
class Link
{
    const SELF_LINK = 'self';
    const NEW_LINK = 'new';
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