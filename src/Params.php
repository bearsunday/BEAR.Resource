<?php

declare(strict_types=1);

namespace BEAR\Resource;

/** @psalm-immutable */
final class Params
{
    /**
     * @param list<string> $required
     * @param list<string> $optional
     *
     * @psalm-pure
     */
    public function __construct(
        public string $method,
        public array $required = [],
        public array $optional = [],
    ) {
    }
}
