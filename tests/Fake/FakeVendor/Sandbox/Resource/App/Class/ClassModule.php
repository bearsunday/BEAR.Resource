<?php

declare(strict_types=1);

namespace BEAR\Resource\FakeVendor\Sandbox\Resource\App\Class;

use Ray\Di\AbstractModule;

class ClassModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(ServiceInterface::class)->to(ServiceImplementation::class);
        $this->bind(ServiceInterface::class)->annotatedWith(OtherQualifier::class)->to(OtherServiceImplementation::class);
        $this->bind(ServiceInterface::class)->annotatedWith('other')->to(OtherServiceImplementation::class);
    }
}
