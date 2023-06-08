<?php

declare(strict_types=1);

namespace BEAR\Resource;

interface ReverseLinkerInterface
{
    /**
     * @param array<string, mixed> $query
     * Return reverse URI
     */
    public function __invoke(string $uri, array $query): string;
}
