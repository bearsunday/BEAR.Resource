<?php

namespace FakeVendor\Blog\Module;

use BEAR\Resource\Module\HalModule;
use BEAR\Resource\Module\ResourceModule;
use Ray\Di\AbstractModule;

class AppModule extends AbstractModule
{
    protected function configure()
    {
        $this->install(new ResourceModule('FakeVendor\Blog'));
        $this->install(new HalModule());
    }
}
