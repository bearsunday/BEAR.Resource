<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Class;

final class SearchCriteria
{
    public function __construct(
        public readonly string $query,
        public readonly ?string $category = null,
    ) {
        if (strlen($this->query) < 2) {
            throw new \InvalidArgumentException('Search query must be at least 2 characters');
        }
    }
}