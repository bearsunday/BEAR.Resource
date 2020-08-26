<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Module;

use BEAR\Resource\Module\HalModule;
use BEAR\Resource\Module\ResourceModule;
use Ray\Di\AbstractModule;

class AppModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new ResourceModule('FakeVendor\Sandbox'));
        $this->install(new HalModule());
    }
}
