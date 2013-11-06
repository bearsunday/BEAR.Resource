<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Adapter;

use BEAR\Resource\ProviderInterface;
use BEAR\Resource\Adapter\Http\Guzzle;
use Guzzle\Service\Client as GuzzleClient;

/**
 * Http resource
 */
class Http implements ProviderInterface, AdapterInterface
{
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function get($uri)
    {
        $instance = new Http\Guzzle(new GuzzleClient($uri));

        return $instance;
    }
}
