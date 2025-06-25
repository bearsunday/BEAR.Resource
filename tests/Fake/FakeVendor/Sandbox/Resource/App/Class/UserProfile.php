<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Class;

final class UserProfile
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly int $age,
        public readonly string $email,
    ) {
        if (strlen($this->firstName) < 2) {
            throw new \InvalidArgumentException('First name must be at least 2 characters');
        }
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
        if ($this->age < 0 || $this->age > 150) {
            throw new \InvalidArgumentException('Age must be between 0 and 150');
        }
    }
}