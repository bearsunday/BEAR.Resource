<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\InputParam;

final class FakePager
{
    public function __construct(
        public readonly int $page = 1,
        public readonly int $limit = 20,
    ) {
    }
}
