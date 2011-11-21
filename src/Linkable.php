<?php
/**
 * BEAR.Resource
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Object as ResourceObject;

/**
* Interface for resource link
*
* @package BEAR.Resource
* @author  Akihito Koriyama <akihito.koriyama@gmail.com>
*/
interface Linkable
{
    public function invoke(ResourceObject $ro, array $links, $linkValue);
}