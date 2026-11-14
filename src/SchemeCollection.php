<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\SchemeException;
use Override;

use function array_key_exists;

final class SchemeCollection implements SchemeCollectionInterface
{
    private string $scheme = '';
    private string $appName = '';

    /** @var AdapterInterface[] */
    private array $collection = [];

    /**
     * {@inheritDoc}
     *
     * @psalm-capabilities read-props|write-this-props
     */
    #[Override]
    public function scheme(string $scheme): SchemeCollectionInterface
    {
        $this->scheme = $scheme;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-capabilities read-props|write-this-props
     */
    #[Override]
    public function host(string $host): SchemeCollectionInterface
    {
        $this->appName = $host;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-capabilities read-props|write-this-props
     */
    #[Override]
    public function toAdapter(AdapterInterface $adapter): SchemeCollectionInterface
    {
        $this->collection[$this->scheme . '://' . $this->appName] = $adapter;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @throws SchemeException
     *
     * @psalm-capabilities read-props
     */
    #[Override]
    public function getAdapter(AbstractUri $uri): AdapterInterface
    {
        $schemeIndex = $uri->scheme . '://' . $uri->host;
        if (! array_key_exists($schemeIndex, $this->collection)) {
            if ($uri->scheme === 'http' || $uri->scheme === 'https') {
                return $this->collection['http://self'];
            }

            throw new SchemeException($uri->scheme . '://' . $uri->host);
        }

        return $this->collection[$schemeIndex];
    }
}
