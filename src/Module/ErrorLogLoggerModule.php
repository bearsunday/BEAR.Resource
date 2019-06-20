<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\ErrorLogLogger;
use BEAR\Resource\LoggerInterface;
use Ray\Di\AbstractModule;

final class ErrorLogLoggerModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(LoggerInterface::class)->to(ErrorLogLogger::class);
    }
}
