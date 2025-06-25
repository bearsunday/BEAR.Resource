<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Class;

final class StructuredUser
{
    public function __construct(
        public readonly string $name,
        public readonly int $age,
        public readonly string $email,
    ) {
        if ($this->age < 0) {
            throw new \InvalidArgumentException('Age must be positive');
        }
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
    }
}