<?php

declare(strict_types=1);

namespace BEAR\Resource;

interface FactoryInterface
{
    /**
     * Return new resource object instance
     *
     * @param AbstractUri|string $uri resource URI
     */
    public function newInstance($uri): ResourceObject;
}
