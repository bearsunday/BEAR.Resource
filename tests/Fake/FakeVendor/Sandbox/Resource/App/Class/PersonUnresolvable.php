<?php

declare(strict_types=1);

namespace BEAR\Resource\FakeVendor\Sandbox\Resource\App\Class;

interface UnresolvableService
{
    public function serve(): string;
}

final class PersonUnresolvable
{
    public function __construct(
        public string $name,
        public UnresolvableService $service,
    ) {}
}