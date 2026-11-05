<?php

declare(strict_types=1);

namespace BEAR\Resource;

/** @psalm-import-type Query from Types */
interface ReverseLinkerInterface
{
    /**
     * @param Query $query
     * Return reverse URI
     */
    public function __invoke(string $uri, array $query): string;
}
