<?php

declare(strict_types=1);

namespace BEAR\Resource\FakeVendor\Sandbox\Resource\App\Class;

use Ray\Di\Di\Named;

final class PersonWithNamedService
{
    public function __construct(
        public string $name,
        public int $age,
        #[Named('other')] public ServiceInterface $service
    ) {}
}
