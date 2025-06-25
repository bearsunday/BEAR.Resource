<?php

declare(strict_types=1);

namespace BEAR\Resource\FakeVendor\Sandbox\Resource\App\Class;

final class PersonWithQualifiedService
{
    public function __construct(
        public string $name,
        public int $age,
        #[OtherQualifier] public ServiceInterface $service
    ) {}
}
