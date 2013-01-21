<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * Interface for resource adapter provider.
 *
 * @package BEAR.Resource
 */
interface ProviderInterface
{
    /**
     * Get resource adapter
     *
     * @param string $uri
     */
    public function get($uri);
}
