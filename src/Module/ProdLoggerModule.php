<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\LoggerInterface;
use BEAR\Resource\ProdLogger;
use Override;
use Ray\Di\AbstractModule;

final class ProdLoggerModule extends AbstractModule
{
    /** @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time */
    #[Override]
    protected function configure(): void
    {
        $this->bind(LoggerInterface::class)->to(ProdLogger::class);
    }
}
