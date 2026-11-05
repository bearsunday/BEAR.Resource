<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\ErrorLogLogger;
use BEAR\Resource\LoggerInterface;
use Override;
use Ray\Di\AbstractModule;

/**
 * Provides LoggerInterface interceptor bindings
 */
final class ErrorLogLoggerModule extends AbstractModule
{
    /** @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time */
    #[Override]
    protected function configure(): void
    {
        $this->bind(LoggerInterface::class)->to(ErrorLogLogger::class);
    }
}
