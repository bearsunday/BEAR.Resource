<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\DevPsrLogger;
use BEAR\Resource\LoggerInterface;
use Ray\Di\AbstractModule;

final class DevLoggerModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(LoggerInterface::class)->to(DevPsrLogger::class);
    }
}
