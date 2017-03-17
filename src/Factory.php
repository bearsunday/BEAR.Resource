<?php
/**
 * This file is part of the BEAR.Sunday package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use Ray\Di\Di\Inject;

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
     * {@inheritdoc}
     *
     * @throws \BEAR\Resource\Exception\UriException
     */
    public function newInstance($uri)
    {
        if (! $uri instanceof Uri) {
            $uri = new Uri($uri);
        }
        $adapter = $this->scheme->getAdapter($uri);
        $resourceObject = $adapter->get($uri);

        return $resourceObject;
    }
}
