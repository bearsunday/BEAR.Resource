<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

interface OptionProviderInterface
{
    /**
     * @param ResourceObject $ro resource object
     *
     * @return ResourceObject
     */
    public function get(ResourceObject $ro);
}
