<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\ResourceNotFound;
use Ray\Di\Di\Inject;
use Ray\Di\Scope;
use Traversable;
use Ray\Di\Exception\Unbound;
/**
 * Resource object factory
 *
 * @Scope("Singleton")
 */
class Factory implements FactoryInterface, \IteratorAggregate
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
        if (substr($uri, -1) === '/') {
            $uri .= 'index';
        }
        list($scheme, $host) = $this->parseUri($uri);
        if (!isset($this->scheme[$scheme][$host])) {
            if (!(isset($this->scheme[$scheme]['*']))) {
                throw new Exception\Scheme($uri);
            }
            $host = '*';
        }
        $adapter = $this->scheme[$scheme][$host];
            /** @var $adapter \BEAR\Resource\Adapter\AdapterInterface */
        try {
            $resourceObject = $adapter->get($uri);
        } catch (Unbound $e) {
            throw new ResourceNotFound($uri, 0 , $e);
        }

        $resourceObject->uri = $uri;

        return $resourceObject;
    }

    /**
     * @param string $uri
     *
     * @return array [$scheme, $host]
     * @throws Exception\Uri
     * @throws Exception\Scheme
     */
    private function parseUri($uri)
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

        return [$scheme, $host];
    }

    /**
     * {@inheritdoc}
     *
     * @return \ArrayIterator|\MultipleIterator|Traversable
     */
    public function getIterator()
    {
        return $this->scheme->getIterator();
    }
}
