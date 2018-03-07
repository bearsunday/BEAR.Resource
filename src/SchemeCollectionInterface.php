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
     *
     * @param string $scheme
     *
     * @return $this
     */
    public function scheme($scheme);

    /**
     * Set host
     *
     * @param string $host
     *
     * @return $this
     */
    public function host($host);

    /**
     * Set resource adapter
     *
     * @param AdapterInterface $adapter
     *
     * @return $this
     */
    public function toAdapter(AdapterInterface $adapter);

    /**
     * Return resource adapter
     *
     * @param AbstractUri $uri
     *
     * @return AdapterInterface
     */
    public function getAdapter(AbstractUri $uri);
}
