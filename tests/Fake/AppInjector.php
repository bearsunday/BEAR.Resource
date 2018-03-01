<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
        $injector = new Injector(new AppModule);

        return $injector->getInstance($interface, $name);
    }
}
