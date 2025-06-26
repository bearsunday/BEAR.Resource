<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\InputParam\Integration;

final class FakeSearchData
{
    /**
     * @param string      $query    Search query
     * @param string|null $category Category filter
     */
    public function __construct(
        public readonly string $query,
        public readonly string|null $category = null,
    ) {
    }
}
