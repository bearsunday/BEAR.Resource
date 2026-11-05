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
     *
     * @psalm-capabilities read-props|write-props|read-globals|write-globals|io|time
     */
    public function getParameters(callable $callable, array $query): array;
}
