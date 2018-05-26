<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

interface SchemeCollectionInterface
{
    /**
     * Set scheme
     */
    public function scheme(string $scheme) : self;

    /**
     * Set host
     */
    public function host(string $host) : self;

    /**
     * Set resource adapter
     */
    public function toAdapter(AdapterInterface $adapter) : self;

    /**
     * Return resource adapter
     */
    public function getAdapter(AbstractUri $uri) : AdapterInterface;
}
