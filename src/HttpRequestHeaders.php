<?php

declare(strict_types=1);

namespace BEAR\Resource;

final class HttpRequestHeaders
{
    /**
     * @param list<string> $headers
     *
     * @psalm-pure
     */
    public function __construct(
        public array $headers = [],
    ) {
    }
}
