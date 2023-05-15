<?php

declare(strict_types=1);

namespace BEAR\Resource;

final class NullReverseLinker implements ReverseLinkerInterface
{
    public function __invoke(string $uri, array $query): string
    {
        return $uri;
    }
}
