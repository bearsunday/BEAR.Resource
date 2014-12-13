<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

interface ResourceRepositoryInterface
{
    /**
     * Fetches an resource object from the repository
     *
     * @param Uri $uri The resource URI entry to fetch.
     *
     * @return ResourceObject The cached ResourceObject data or FALSE, if no ResourceObject entry exists for the given URI.
     */
    public function fetch(Uri $uri);

    /**
     * Tests if a resource object entry exists in the repository
     *
     * @param Uri $uri The resource URI of the entry to check for.
     *
     * @return boolean TRUE if a ResourceObject exists for the given URI, FALSE otherwise.
     */
    public function contains(Uri $uri);

    /**
     * Store ResourceObject into the repository
     *
     * @param ResourceObject $resourceObject Resource object
     * @param int            $lifeTime       If != 0, sets a specific lifetime for this cache entry (0 => infinite lifeTime).
     *
     * @return ResourceObject
     */
    public function save(ResourceObject $resourceObject, $lifeTime = 0);

    /**
     * Deletes a ResourceObject entry
     *
     * @param Uri $uri The resource URI of the delete entry.
     *
     * @return boolean TRUE if the ResourceObject was successfully deleted, FALSE otherwise.
     */
    public function delete(Uri $uri);
}
