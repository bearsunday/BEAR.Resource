<?php

declare(strict_types=1);

namespace BEAR\Resource;

/** @deprecated  Use NullReverseLinker instead */
final class NullReverseLink implements ReverseLinkInterface
{
    public function __invoke(string $uri): string
    {
        return $uri;
    }
}
