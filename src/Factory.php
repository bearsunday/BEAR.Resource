<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\ResourceNotFoundException;
use BEAR\Resource\Exception\UriException;
use Ray\Di\Di\Inject;
use Ray\Di\Exception\Unbound;

class Factory implements FactoryInterface
{
    /**
     * Resource adapter biding config
     *
     * @var SchemeCollection
     */
    private $scheme;

    /**
     * @param SchemeCollectionInterface $scheme
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
     * @codeCoverageIgnore
     */
    public function setSchemeCollection(SchemeCollectionInterface $scheme)
    {
        $this->scheme = $scheme;
    }

    /**
     * {@inheritDoc}
     */
    public function newInstance($uri)
    {
        if (is_string($uri)) {
            $uri = new Uri($uri);
        }
        if (is_scalar($uri) || ! $uri instanceof Uri) {
            $msg = is_object($uri) ? get_class($uri) : gettype($uri);
            throw new UriException($msg);
        }
        $adapter = $this->scheme->getAdapter($uri);
        try {
            $resourceObject = $adapter->get($uri);
        } catch (Unbound $e) {
            $resourceObject = $this->retryWithIndexSuffix($e, $uri);
        }

        return $resourceObject;
    }

    /**
     * @param Unbound $e
     * @param Uri     $uri
     *
     * @return ResourceObject
     */
    private function retryWithIndexSuffix(Unbound $e, Uri $uri)
    {
        if (substr($uri->path, -1) !== '/' || substr($uri->path, -6) === '/index') {
            throw new ResourceNotFoundException($uri, 404, $e);
        }
        $uri .= 'index';

        return $this->newInstance((string) $uri);
    }
}
