<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Adapter;

use Guzzle\Service\Client as GuzzleClient;

/**
 * Http resource adapter
 */
class Http implements AdapterInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($uri)
    {
        $instance = new Http\Guzzle(new GuzzleClient($uri));

        return $instance;
    }
}
