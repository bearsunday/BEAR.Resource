<?php

declare(strict_types=1);

namespace BEAR\Resource;

final class HttpRequestHeaders
{
    /** @param array<string> $headers */
    public function __construct(
        public array $headers = [],
    ) {
    }
}
