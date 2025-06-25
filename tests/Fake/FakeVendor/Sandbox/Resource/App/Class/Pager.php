<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Class;

final class Pager
{
    public function __construct(
        public readonly int $page = 1,
        public readonly int $limit = 20,
    ) {
        if ($this->page < 1) {
            throw new \InvalidArgumentException('Page number must be 1 or greater');
        }
        
        if ($this->limit < 1 || $this->limit > 100) {
            throw new \InvalidArgumentException('Limit must be between 1 and 100');
        }
    }
    
    public function getOffset(): int
    {
        return ($this->page - 1) * $this->limit;
    }
}