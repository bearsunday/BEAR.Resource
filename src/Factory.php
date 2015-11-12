<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\ResourceNotFoundException;
use Ray\Di\Di\Inject;
use Ray\Di\Exception\Unbound;

final class Factory implements FactoryInterface
{
    /**
     * Resource adapter biding config
     *
     * @var SchemeCollectionInterface
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
        if (! $uri instanceof Uri) {
            $uri = new Uri($uri);
        }
        $adapter = $this->scheme->getAdapter($uri);
        try {
            $resourceObject = $adapter->get($uri);
        } catch (Unbound $e) {
            throw new ResourceNotFoundException($uri, 404, $e);
        }

        return $resourceObject;
    }
}
