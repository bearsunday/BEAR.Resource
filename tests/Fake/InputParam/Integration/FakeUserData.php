<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\InputParam\Integration;

final class FakeUserData
{
    /**
     * @param string $name User name
     * @param int    $age  User age
     */
    public function __construct(
        public readonly string $name,
        public readonly int $age,
    ) {
    }
}
