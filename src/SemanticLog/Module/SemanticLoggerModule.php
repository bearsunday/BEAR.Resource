<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog\Module;

use BEAR\Resource\Invoker;
use BEAR\Resource\InvokerInterface;
use BEAR\Resource\SemanticLog\ContextFactoryInterface;
use BEAR\Resource\SemanticLog\Profile\Compact\ContextFactory;
use BEAR\Resource\SemanticLog\SemanticInvoker;
use Koriym\SemanticLogger\SemanticLogger;
use Koriym\SemanticLogger\SemanticLoggerInterface;
use Override;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

final class SemanticLoggerModule extends AbstractModule
{
    #[Override]
    protected function configure(): void
    {
        $this->bind(SemanticLoggerInterface::class)->to(SemanticLogger::class)->in(Scope::SINGLETON);
        $this->bind(ContextFactoryInterface::class)->to(ContextFactory::class)->in(Scope::SINGLETON);

        // Bind the original invoker with annotation so our adapter can inject it
        $this->bind(InvokerInterface::class)->annotatedWith('original')->to(Invoker::class);

        // Override the default invoker with our semantic logging adapter
        $this->bind(InvokerInterface::class)->to(SemanticInvoker::class);
    }
}
