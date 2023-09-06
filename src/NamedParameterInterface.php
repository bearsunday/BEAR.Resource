<?php

declare(strict_types=1);

namespace BEAR\Resource;

interface NamedParameterInterface
{
    /**
     * Return ordered parameters from named query
     *
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     */
    public function getParameters(callable $callable, array $query): array;
}
