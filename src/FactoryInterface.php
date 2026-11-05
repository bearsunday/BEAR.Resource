<?php

declare(strict_types=1);

namespace BEAR\Resource;

interface FactoryInterface
{
    /**
     * Return new resource object instance
     *
     * @param AbstractUri|string $uri resource URI
     *
     * @psalm-capabilities read-props|write-props|read-globals|write-globals|io|time
     */
    public function newInstance($uri): ResourceObject;
}
