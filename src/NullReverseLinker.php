<?php

declare(strict_types=1);

namespace BEAR\Resource;

final class NullReverseLinker implements ReverseLinkerInterface
{
    /** @param array<string, mixed> $query */
    public function __invoke(string $uri, array $query): string
    {
        return $uri;
    }
}
