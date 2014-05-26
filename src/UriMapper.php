<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

class UriMapper implements UriMapperInterface
{
    /**
     * @var string
     */
    private $apiPath = 'api';

    /**
     * @param string $apiPath
     *
     * @Inject(optional = true)
     * @Named("api_path")
     */
    public function setApiPath($apiPath)
    {
        $this->apiPath = $apiPath;
    }

    /**
     * {@inheritdoc}
     */
    public function map($requestUri)
    {
        $firstSlashPos = strpos($requestUri, '/');
        $uri = sprintf(
            "%s://self%s",
            substr($requestUri, 0, $firstSlashPos),
            substr($requestUri, $firstSlashPos)
        );

        return $uri;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseMap($internalUri)
    {
        if (! filter_var($internalUri, FILTER_VALIDATE_URL)) {
            throw new Exception\Uri($internalUri);
        }
        $parsedUrl = parse_url($internalUri);
        $uri = sprintf(
            '/%s%s',
            $this->apiPath,
            $parsedUrl['path']
        );
        if (isset($parsedUrl['query'])) {
            $uri .= '?' . $parsedUrl['query'];
        }

        return $uri;
    }
}
