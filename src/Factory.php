<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\UriException;
use Override;
use Ray\Di\Di\Inject;

use function is_string;

final class Factory implements FactoryInterface
{
    /** @psalm-pure */
    public function __construct(
        private SchemeCollectionInterface $scheme,
        private readonly UriFactory $uri,
    ) {
    }

    /**
     * Set scheme collection
     *
     * @codeCoverageIgnore
     * @psalm-capabilities read-props|write-this-props
     */
    #[Inject(optional: true)]
    public function setSchemeCollection(SchemeCollectionInterface $scheme): void
    {
        $this->scheme = $scheme;
    }

    /**
     * {@inheritDoc}
     *
     * @throws UriException
     */
    #[Override]
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
