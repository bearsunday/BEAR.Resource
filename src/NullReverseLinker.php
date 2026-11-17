<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Override;

/** @psalm-import-type Query from Types */
final class NullReverseLinker implements ReverseLinkerInterface
{
    /**
     * @param Query $query
     *
     * @psalm-pure
     */
    #[Override]
    public function __invoke(string $uri, array $query): string
    {
        return $uri;
    }
}
