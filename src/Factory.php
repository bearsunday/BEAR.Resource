<?php

declare(strict_types=1);

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
     * @var UriFactory
     */
    private $uri;

    public function __construct(SchemeCollectionInterface $scheme, UriFactory $uri)
    {
        $this->scheme = $scheme;
        $this->uri = $uri;
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
            $uri = ($this->uri)($uri);
        }
        $ro = $this->scheme->getAdapter($uri)->get($uri);
        $ro->uri = $uri;

        return $ro;
    }
}
