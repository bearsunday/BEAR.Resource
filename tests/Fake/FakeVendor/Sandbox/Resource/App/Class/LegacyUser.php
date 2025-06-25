<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Class;

// This class mimics ClassParam behavior - no #[Input] attribute
final class LegacyUser
{
    public function __construct(
        public readonly string $name,
        public readonly int $age,
        public readonly string $email,
    ) {
        if ($this->age < 0) {
            throw new \InvalidArgumentException('Age must be positive');
        }
    }
}