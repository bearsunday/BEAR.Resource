<?php

declare(strict_types=1);

namespace BEAR\Resource;

/** @internal */
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
