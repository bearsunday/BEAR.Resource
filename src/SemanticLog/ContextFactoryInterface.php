<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog;

use BEAR\Resource\AbstractRequest;
use BEAR\Resource\ResourceObject;
use Koriym\SemanticLogger\AbstractContext;
use Throwable;

/**
 * Factory for creating semantic logging contexts
 *
 * Provides context objects for different stages of resource request lifecycle.
 * Implementations can vary in detail level (Verbose with profiling, Compact without).
 */
interface ContextFactoryInterface
{
    /**
     * Create context for resource request initiation
     */
    public function createOpenContext(AbstractRequest $request): AbstractContext;

    /**
     * Create context for successful resource completion
     */
    public function createCompleteContext(ResourceObject $resource, AbstractContext $openContext): AbstractContext;

    /**
     * Create context for resource error handling
     */
    public function createErrorContext(Throwable $exception, string $exceptionId = '', ?AbstractContext $openContext = null): AbstractContext;
}
