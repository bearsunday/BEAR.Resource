<?php

declare(strict_types=1);

namespace BEAR\Resource;

interface SchemeCollectionInterface
{
    /**
     * Set scheme
     *
     * @psalm-capabilities read-props|write-this-props
     */
    public function scheme(string $scheme): self;

    /**
     * Set host
     *
     * @psalm-capabilities read-props|write-this-props
     */
    public function host(string $host): self;

    /**
     * Set resource adapter
     *
     * @psalm-capabilities read-props|write-this-props
     */
    public function toAdapter(AdapterInterface $adapter): self;

    /**
     * Return resource adapter
     *
     * @psalm-capabilities read-props
     */
    public function getAdapter(AbstractUri $uri): AdapterInterface;
}
