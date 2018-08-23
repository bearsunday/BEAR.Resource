<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

interface FactoryInterface
{
    /**
     * Return new resource object instance
     *
     * @param string|AbstractUri $uri resource URI
     */
    public function newInstance($uri) : ResourceObject;
}
