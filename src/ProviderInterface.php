<?php
/**
 * This file is part of the BEAR.Sunday package.
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
