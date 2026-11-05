<?php

declare(strict_types=1);

namespace BEAR\Resource;

/** @psalm-immutable */
final class ImportApp
{
    /** @psalm-pure */
    public function __construct(
        public string $host,
        public string $appName,
        public string $context,
    ) {
    }
}
