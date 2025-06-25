<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Class;

final class SimpleTestObject
{
    public function __construct(
        public string $name,
        public int $age = 25,
    ) {}
}