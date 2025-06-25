<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Class;

final class CamelCaseEntity
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $fullName,
        public readonly string $emailAddress,
        public readonly int $phoneNumber,
    ) {
    }
}