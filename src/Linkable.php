<?php
/**
 * BEAR.Resource
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Object as ResourceObject;

interface Linkable
{
    public function invoke(ResourceObject $ro, array $links, $linkValue);
}