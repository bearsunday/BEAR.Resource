<?php
/**
 * BEAR.Resource
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Object as ResourceObject;

class Link
{
    const SELF_LINK = 'self';
    const NEW_LINK = 'new';
    const CRAWL_LINK = 'crawl';

    public $key;
    public $type;
}