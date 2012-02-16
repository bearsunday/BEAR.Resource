<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Adapter;

use BEAR\Resource\Object as ResourceObject,
    BEAR\Resource\Provider,
    BEAR\Resource\Adapter\Http\Guzzle;

use Guzzle\Service\Client as GuzzleClient;


/**
 * App resource (app:://self/path/to/resource)
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 *
 * @Scope("singleton")
 */
class Http implements ResourceObject, Provider
{
    /**
     * Constructor
     *
     */
    public function __construct()
    {
    }

    /**
     * (non-PHPdoc)
     *
     * @see    BEAR\Resource.Provider::get()new
     * @return object
     * @throws Exception\InvalidHost
     */
    public function get($uri)
    {
        $instance = new Http\Guzzle(new GuzzleClient($uri));
        return $instance;
    }
}
