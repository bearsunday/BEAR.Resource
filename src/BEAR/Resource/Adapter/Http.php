<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Adapter;

use BEAR\Resource\Object as ResourceObject;
use BEAR\Resource\Provider;
use BEAR\Resource\Adapter\Http\Guzzle;
use Guzzle\Service\Client as GuzzleClient;

/**
 * App resource (app:://self/path/to/resource)
 *
 * @package BEAR.Resource
 *
 * @Scope("singleton")
 */
class Http implements ResourceObject, Provider, Adapter
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
     * @see BEAR\Resource.Provider::get()
     */
    public function get($uri)
    {
        $instance = new Http\Guzzle(new GuzzleClient($uri));

        return $instance;
    }
}
