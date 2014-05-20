<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Adapter;

use BEAR\Resource\Exception\ResourceNotFound;
use GuzzleHttp\Client;
use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use BEAR\Resource\Adapter\Web\WebClient;

/**
 * Web api resource adapter
 *
 * web://{service}/{resourceName}/ + query
 */
class Web implements AdapterInterface
{
    /**
     * @var string[]
     */
    private $webDirs;

    public function __construct(
        array $webDirs
    ) {
        $this->webDirs = $webDirs;
    }

    /**
     * {@inheritdoc}
     */
    public function get($uri)
    {
        $parsedUrl = parse_url($uri);
        $service = $parsedUrl['host'];
        $path = $parsedUrl['path'];
        foreach ($this->webDirs as $webDir) {
            $serviceFile = $webDir . '/' . $service . '.php';
            if (file_exists($serviceFile)) {
                $serviceDescription = require $serviceFile;
                return $this->getAdapter($serviceDescription, $path);
            }
        }
        throw new ResourceNotFound($uri);
    }

    /**
     * @param array $serviceDescription
     *
     * @return GuzzleClient
     */
    private function getAdapter(array $serviceDescription, $path)
    {
        $description = new Description($serviceDescription);
        $instance = new WebClient(
            new GuzzleClient(
                new Client,
                $description
            ),
            $serviceDescription,
            $path
        );
        return $instance;
    }
}
