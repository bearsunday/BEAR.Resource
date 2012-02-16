<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Object as ResourceObject;

/**
 * Interface for resource link
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 * 
 */
interface Linkable
{
    /**
     * Invokable link
     *
     * @param ResourceObject $ro
     * @param array          $links
     * @param mixed          $linkValue resource output value
     *
     * @return mixed link result
     */
    public function invoke(ResourceObject $ro, array $links, $linkValue);
}