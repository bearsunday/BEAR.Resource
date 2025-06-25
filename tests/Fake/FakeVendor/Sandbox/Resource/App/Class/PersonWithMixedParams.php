<?php

declare(strict_types=1);

namespace BEAR\Resource\FakeVendor\Sandbox\Resource\App\Class;

final class PersonWithMixedParams
{
    public function __construct(
        public string $name,
        public string $title = 'Mr.',
        public string $country = 'Japan',
    ) {}
}