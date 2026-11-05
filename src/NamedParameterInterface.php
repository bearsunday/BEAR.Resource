<?php

declare(strict_types=1);

namespace BEAR\Resource;

/** @psalm-import-type Query from Types */
interface NamedParameterInterface
{
    /**
     * Return ordered parameters from named query
     *
     * @param Query $query
     *
     * @return Query
     */
    public function getParameters(callable $callable, array $query): array;
}
