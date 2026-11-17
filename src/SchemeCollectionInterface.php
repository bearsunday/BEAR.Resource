<?php

declare(strict_types=1);

namespace BEAR\Resource;

interface SchemeCollectionInterface
{
    /**
     * Set scheme
     */
    public function scheme(string $scheme): self;

    /**
     * Set host
     */
    public function host(string $host): self;

    /**
     * Set resource adapter
     */
    public function toAdapter(AdapterInterface $adapter): self;

    /**
     * Return resource adapter
     *
     * @psalm-capabilities read-props
     */
    public function getAdapter(AbstractUri $uri): AdapterInterface;
}
