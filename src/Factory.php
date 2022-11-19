<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\UriException;
use Ray\Di\Di\Inject;

use function is_string;

final class Factory implements FactoryInterface
{
    public function __construct(
        private SchemeCollectionInterface $scheme,
        private UriFactory $uri,
    ) {
    }

    /**
     * Set scheme collection
     *
     * @Inject(optional=true)
     * @codeCoverageIgnore
     */
    #[Inject(optional: true)]
    public function setSchemeCollection(SchemeCollectionInterface $scheme): void
    {
        $this->scheme = $scheme;
    }

    /**
     * {@inheritdoc}
     *
     * @throws UriException
     */
    public function newInstance($uri): ResourceObject
    {
        if (is_string($uri)) {
            $uri = ($this->uri)($uri);
        }

        $ro = $this->scheme->getAdapter($uri)->get($uri);
        $ro->uri = $uri;

        return $ro;
    }
}
