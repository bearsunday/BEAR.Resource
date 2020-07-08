<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\LoggerInterface;
use BEAR\Resource\ProdLogger;
use Ray\Di\AbstractModule;

final class ProdLoggerModule extends AbstractModule
{
    protected function configure() : void
    {
        $this->bind(LoggerInterface::class)->to(ProdLogger::class);
    }
}
