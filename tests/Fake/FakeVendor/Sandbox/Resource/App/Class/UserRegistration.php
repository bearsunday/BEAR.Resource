<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Class;

use BEAR\Resource\Annotation\Input;

final class UserRegistration
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly int $age,
        #[Input] public readonly Address $address,
    ) {
        if ($this->age < 0 || $this->age > 150) {
            throw new \InvalidArgumentException('Age must be between 0 and 150');
        }
    }
}