<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Adapter;

use GuzzleHttp\Client;

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
        $instance = new Http\Guzzle(
            new Client(['base_url' => [$uri, []]])
        );

        return $instance;
    }
}
