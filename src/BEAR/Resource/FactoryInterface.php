<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Di\Di\ImplementedBy;

/**
 * Interface for resource factory
 *
 * @package BEAR.Resource
 *
 * @ImplementedBy("Factory")
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
