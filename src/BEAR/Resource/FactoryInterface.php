<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * Interface for resource factory
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 */
interface FactoryInterface
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
