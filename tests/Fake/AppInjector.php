<?php

declare(strict_types=1);

namespace BEAR\Package;

use FakeVendor\Sandbox\Module\AppModule;
use Ray\Di\Injector;
use Ray\Di\InjectorInterface;
use Ray\Di\Name;

/**
 * Fake AppInjector for unit test
 */
class AppInjector implements InjectorInterface
{
    public function __construct($appName, $context)
    {
        unset($appName, $context);
    }

    public function getInstance($interface, $name = Name::ANY)
    {
        $injector = new Injector(new AppModule(), __DIR__ . '/tmp');

        return $injector->getInstance($interface, $name);
    }
}
