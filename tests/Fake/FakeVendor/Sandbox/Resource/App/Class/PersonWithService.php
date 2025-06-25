<?php

declare(strict_types=1);

namespace BEAR\Resource\FakeVendor\Sandbox\Resource\App\Class;

use Ray\Di\Di\Named;

final class PersonWithService
{
    public function __construct(
        public string $name,
        public int $age,
        public ServiceInterface $service,
    ) {}
}
