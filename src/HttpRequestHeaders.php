<?php

declare(strict_types=1);

namespace BEAR\Resource;

/** @psalm-immutable */
final class HttpRequestHeaders
{
    /** @param list<string> $headers */
    public function __construct(
        public array $headers = [],
    ) {
    }
}
