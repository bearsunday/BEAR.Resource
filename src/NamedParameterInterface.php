<?php

declare(strict_types=1);

namespace BEAR\Resource;

interface NamedParameterInterface
{
    /**
     * Return ordered parameters from named query
     */
    public function getParameters(callable $callable, array $query) : array;
}
