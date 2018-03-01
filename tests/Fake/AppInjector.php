<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package;

use Ray\Di\InjectorInterface;
use Ray\Di\Name;

class AppInjector implements InjectorInterface
{
    public function __construct(string $appName, string $context)
    {
    }

    public function getInstance($interface, $name = Name::ANY)
    {
    }
}
