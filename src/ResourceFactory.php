<?php
/**
 * BEAR.Resource
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Object as ResourceObject;

/**
 * Interface for resource factory
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 * @ImplementedBy("BEAR\Resource\Factory")
 */
interface ResourceFactory
{
    /**
     * Return new resource object instance
     *
     * @param string $uri resource URI
     *
     * @return \BEAR\Resource\Object;
     */
    public function newInstance($uri);
}