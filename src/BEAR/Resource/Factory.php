<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Scope;
use Ray\Di\Exception\NotReadable;

/**
 * Resource object factory
 *
 * @Scope("singleton")
 */
class Factory implements FactoryInterface
{
    /**
     * Resource adapter biding config
     *
     * @var SchemeCollection
     */
    private $scheme = [];

    /**
     * @param SchemeCollectionInterface $scheme
     *
     * @Inject
     */
    public function __construct(SchemeCollectionInterface $scheme)
    {
        $this->scheme = $scheme;
    }

    /**
     * Set scheme collection
     *
     * @param SchemeCollectionInterface $scheme
     *
     * @Inject(optional = true)
     */
    public function setSchemeCollection(SchemeCollectionInterface $scheme)
    {
        $this->scheme = $scheme;
    }

    /**
     * {@inheritDoc}
     * @throws Exception\Scheme
     */
    public function newInstance($uri)
    {
        $parsedUrl = parse_url($uri);
        if (!(isset($parsedUrl['scheme']) && isset($parsedUrl['scheme']))) {
            throw new Exception\Uri;
        }
        $scheme = $parsedUrl['scheme'];
        $host = $parsedUrl['host'];
        if (!isset($this->scheme[$scheme])) {
            throw new Exception\Scheme($uri);
        }
        if (!isset($this->scheme[$scheme][$host])) {
            if (!(isset($this->scheme[$scheme]['*']))) {
                throw new Exception\Scheme($uri);
            }
            $host = '*';
        }
        try {
            $adapter = $this->scheme[$scheme][$host];
            /** @var $adapter \BEAR\Resource\Adapter\AdapterInterface */
            $resourceObject = $adapter->get($uri);
        } catch (NotReadable $e) {
            $resourceObject = $this->indexRequest($uri, $e);
        }

        $resourceObject->uri = $uri;

        return $resourceObject;
    }

    /**
     * @param string      $uri
     * @param NotReadable $e
     *
     * @return ResourceObject
     * @throws Exception\ResourceNotFound
     */
    private function indexRequest($uri, NotReadable $e)
    {
        if (substr($uri, -1) !== '/') {
            throw new Exception\ResourceNotFound($uri, 0, $e);
        }
        $resourceObject = $this->newInstance($uri . 'index');

        return $resourceObject;
    }
}
