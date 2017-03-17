<?php
/**
 * This file is part of the BEAR.Sunday package.
 *
 * @license http://opensource.org/licenses/MIT MIT
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
