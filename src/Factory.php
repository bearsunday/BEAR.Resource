<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
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

    public function __construct(SchemeCollectionInterface $scheme)
    {
        $this->scheme = $scheme;
    }

    /**
     * Set scheme collection
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
    public function newInstance($uri) : ResourceObject
    {
        if (is_string($uri)) {
            $uri = new Uri($uri);
        }
        $adapter = $this->scheme->getAdapter($uri);

        return $adapter->get($uri);
    }
}
