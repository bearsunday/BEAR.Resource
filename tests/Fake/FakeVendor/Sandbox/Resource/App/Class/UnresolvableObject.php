<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Class;

interface UnknownService
{
    public function serve(): string;
}

final class UnresolvableObject
{
    public function __construct(
        public string $name,
        public UnknownService $service,
    ) {}
}