<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog;

use BEAR\Resource\AbstractRequest;
use BEAR\Resource\InvokerInterface;
use BEAR\Resource\ResourceObject;
use Koriym\SemanticLogger\SemanticLoggerInterface;
use Override;
use Ray\Di\Di\Named;
use Throwable;

/**
 * Development semantic invoker with log persistence for MCP integration
 *
 * Orchestrates semantic logging with immediate file persistence for AI-assisted debugging.
 * Follows single responsibility principle by delegating persistence to DevLogPersister.
 */
final class DevSemanticInvoker implements InvokerInterface
{
    public function __construct(
        #[Named('original')]
        private InvokerInterface $invoker,
        private SemanticLoggerInterface $logger,
        private ContextFactoryInterface $factory,
        private DevLogPersister $persister,
    ) {
    }

    #[Override]
    public function invoke(AbstractRequest $request): ResourceObject
    {
        $openContext = $this->factory->createOpenContext($request);
        $openId = $this->logger->open($openContext);

        try {
            $result = $this->invoker->invoke($request);
            $closeContext = $this->factory->createCompleteContext($result, $openContext);
            $this->logger->close($closeContext, $openId);

            // Persist logs after successful completion
            $this->persister->persistLogs($this->logger);

            return $result;
        } catch (Throwable $e) {
            $errorContext = $this->factory->createErrorContext($e);
            $this->logger->close($errorContext, $openId);

            // Persist logs after error handling
            $this->persister->persistLogs($this->logger);

            throw $e;
        }
    }
}
