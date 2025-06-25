<?php

declare(strict_types=1);

namespace BEAR\Resource\FakeVendor\Sandbox\Resource\App\Class;

class SimpleUnresolvableService
{
    public function __construct(
        public string $value,
    ) {}
}

final class PersonWithUnresolvableParam
{
    public function __construct(
        public string $name,
        public SimpleUnresolvableService $service,
    ) {}
}