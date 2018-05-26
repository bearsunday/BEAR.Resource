<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\SchemeException;

final class SchemeCollection implements SchemeCollectionInterface
{
    /**
     * Scheme
     *
     * @var string
     */
    private $scheme = '';

    /**
     * Application name
     *
     * @var string
     */
    private $app = '';

    /**
     * @var AdapterInterface[]
     */
    private $collection = [];

    /**
     * {@inheritdoc}
     */
    public function scheme(string $scheme) : SchemeCollectionInterface
    {
        $this->scheme = $scheme;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function host(string $host) : SchemeCollectionInterface
    {
        $this->app = $host;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toAdapter(AdapterInterface $adapter) : SchemeCollectionInterface
    {
        $this->collection[$this->scheme . '://' . $this->app] = $adapter;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws SchemeException
     */
    public function getAdapter(AbstractUri $uri) : AdapterInterface
    {
        $schemeIndex = $uri->scheme . '://' . $uri->host;
        if (! array_key_exists($schemeIndex, $this->collection)) {
            throw new SchemeException($uri->scheme . '://' . $uri->host);
        }

        return $this->collection[$schemeIndex];
    }
}
