<?php
/**
 * BEAR.Resource
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Object as ResourceObject;

/**
 * Resource factory interface
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 */
interface ResourceFactory
{
    /**
     * Return new resource object instance
     *
     * @param string $uri
     * @param array  $defaultQuery
     *
     * @return \BEAR\Resource\Object;
     */
    public function newInstance($uri, $defaultQuery = array());
}

