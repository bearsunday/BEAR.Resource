<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\DevLogger;
use BEAR\Resource\LoggerInterface;
use Ray\Di\AbstractModule;

/**
 * Provides LoggerInterface bindings
 */
final class DevLoggerModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(LoggerInterface::class)->to(DevLogger::class);
    }
}
