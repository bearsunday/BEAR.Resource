<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\InputParam;

final class FakeUser
{
    public function __construct(
        public readonly string $name,
        public readonly int $age,
    ) {
    }
}
