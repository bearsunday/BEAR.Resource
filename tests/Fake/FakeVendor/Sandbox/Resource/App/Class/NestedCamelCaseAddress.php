<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Class;

final class NestedCamelCaseAddress
{
    public function __construct(
        public readonly string $streetName,
        public readonly string $cityName,
        public readonly string $zipCode,
    ) {
    }
}