<?php

declare(strict_types=1);

namespace BEAR\Resource;

/** @deprecated  */
interface ReverseLinkInterface
{
    /**
     * Return reverse URI
     */
    public function __invoke(string $uri): string;
}
