<?php

declare(strict_types=1);

namespace BEAR\Resource;

final class Params
{
    /**
     * @param list<string> $required
     * @param list<string> $optional
     */
    public function __construct(
        public string $method,
        public array $required = [],
        public array $optional = [],
    ) {
    }
}
