<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Class;

final class Address
{
    public function __construct(
        public readonly string $prefecture,
        public readonly string $city,
        public readonly string $street,
        public readonly string $zipCode,
    ) {
        if (!preg_match('/^\d{3}-\d{4}$/', $this->zipCode)) {
            throw new \InvalidArgumentException('Zip code must be in 000-0000 format');
        }
    }
}