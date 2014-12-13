<?php
/**
 * Created by PhpStorm.
 * User: akihito
 * Date: 14/12/13
 * Time: 9:43
 */

namespace BEAR\Resource;

use Doctrine\Common\Cache\Cache;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

final class ResourceRepository implements ResourceRepositoryInterface
{
    /**
     * @var Cache
     */
    private $cache;

    /**
     * @param Cache $cache
     *
     * @Inject
     * @Named("resource_repo")
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(Uri $uri)
    {
        return $this->cache->fetch((string) $uri);
    }

    /**
     * {@inheritdoc}
     */
    public function contains(Uri $uri)
    {
        return $this->cache->contains((string) $uri);
    }

    /**
     * {@inheritdoc}
     */
    public function save(ResourceObject $resourceObject, $lifeTime = 0)
    {
        return $this->cache->save((string) $resourceObject->uri, $resourceObject, $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Uri $uri)
    {
        return $this->cache->delete((string) $uri);
    }
}
