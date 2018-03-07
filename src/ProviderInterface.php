<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

interface ProviderInterface
{
    /**
     * Return new resource object
     *
     * @param AbstractUri $uri
     *
     * @return ResourceObject
     */
    public function get(AbstractUri $uri);
}
