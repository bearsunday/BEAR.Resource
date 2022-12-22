<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Compiler\LazyModuleInterface;
use Ray\Di\AbstractModule;

final class FakeLazyModule implements LazyModuleInterface
{
    public function __construct(private AbstractModule $module)
    {
    }

    public function __invoke(): AbstractModule
    {
        return $this->module;
    }
}
