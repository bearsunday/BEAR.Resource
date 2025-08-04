<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog\Module;

use BEAR\Resource\Invoker;
use BEAR\Resource\InvokerInterface;
use BEAR\Resource\SemanticLog\ContextFactoryInterface;
use BEAR\Resource\SemanticLog\DevLogPersister;
use BEAR\Resource\SemanticLog\DevSemanticInvoker;
use BEAR\Resource\SemanticLog\Profile\Verbose\ContextFactory;
use Koriym\SemanticLogger\SemanticLogger;
use Koriym\SemanticLogger\SemanticLoggerInterface;
use Override;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

use function sys_get_temp_dir;

/**
 * Development semantic logging module with file persistence
 *
 * Provides immediate log file writing for MCP server integration and AI-assisted debugging.
 * Uses Verbose Profile system with complete XHProf, Xdebug, and PHP backtrace data.
 */
final class DevSemanticLoggerModule extends AbstractModule
{
    public function __construct(
        private string $logDirectory = '',
    ) {
        parent::__construct();

        if ($this->logDirectory !== '') {
            return;
        }

        $this->logDirectory = sys_get_temp_dir();
    }

    #[Override]
    protected function configure(): void
    {
        // Use Verbose Context Factory for complete Profile data
        $this->bind(ContextFactoryInterface::class)->to(ContextFactory::class)->in(Scope::SINGLETON);

        // Semantic logger
        $this->bind(SemanticLoggerInterface::class)->to(SemanticLogger::class)->in(Scope::SINGLETON);

        // Dev log persister with configurable directory
        $this->bind(DevLogPersister::class)->to(DevLogPersister::class)->in(Scope::SINGLETON);
        $this->bind()->annotatedWith('dev_log_directory')->toInstance($this->logDirectory);

        // Bind the original invoker with annotation so our dev invoker can inject it
        $this->bind(InvokerInterface::class)->annotatedWith('original')->to(Invoker::class);

        // Override the default invoker with our dev semantic invoker
        $this->bind(InvokerInterface::class)->to(DevSemanticInvoker::class);
    }
}
