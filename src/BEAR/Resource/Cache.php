<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

// use Guzzle\Common\Cache\CacheAdapterInterface as GuzzleCacheAdapter;
use Guzzle\Common\Cache\AbstractCacheAdapter as GuzzleAbstractCacheAdapter;

/**
 * Cache interface
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 *
 */
abstract class Cache extends \Guzzle\Common\Cache\AbstractCacheAdapter
{
}
