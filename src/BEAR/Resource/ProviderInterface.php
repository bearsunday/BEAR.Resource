<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * Interface for resource adapter provider.
 */
interface ProviderInterface
{
    /**
     * Return new resource object
     *
     * @param string $uri
     *
     * @return ResourceObject
     */
    public function get($uri);
}
