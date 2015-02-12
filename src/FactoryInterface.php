<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

interface FactoryInterface
{
    /**
     * Return new resource object instance
     *
     * @param string $uri resource URI
     *
     * @return ResourceObject
     */
    public function newInstance($uri);
}
