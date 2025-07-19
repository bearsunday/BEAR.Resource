<?php

declare(strict_types=1);

namespace BEAR\SchemaLogger\Module;

use BEAR\Resource\InvokerInterface;
use BEAR\SchemaLogger\ResourceInvokerAdapter;
use Koriym\SchemaLogger\SchemaLogger;
use Koriym\SchemaLogger\SchemaLoggerInterface;
use Override;
use Ray\Di\AbstractModule;

final class SchemaLoggerModule extends AbstractModule
{
    #[Override]
    protected function configure(): void
    {
        $this->bind(SchemaLoggerInterface::class)->to(SchemaLogger::class);
        $this->bind(InvokerInterface::class)->to(ResourceInvokerAdapter::class);
    }
}