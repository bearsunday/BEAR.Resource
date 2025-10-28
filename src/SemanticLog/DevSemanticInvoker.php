<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog;

use BEAR\Resource\AbstractRequest;
use BEAR\Resource\InvokerInterface;
use BEAR\Resource\ResourceObject;
use Koriym\SemanticLogger\DevLogger;
use Koriym\SemanticLogger\SemanticLoggerInterface;
use Override;
use Ray\Di\Di\Named;
use Throwable;

use function spl_object_hash;

/**
 * Development semantic invoker with log persistence for MCP integration
 *
 * Orchestrates semantic logging with immediate file persistence for AI-assisted debugging.
 * Follows single responsibility principle by delegating persistence to vendor DevLogger.
 */
final class DevSemanticInvoker implements InvokerInterface
{
    public function __construct(
        #[Named('original')]
        private InvokerInterface $invoker,
        private SemanticLoggerInterface $logger,
        private ContextFactoryInterface $factory,
        private DevLogger $devLogger,
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

            try {
                $this->logger->close($closeContext, $openId);
            } catch (Throwable) {
                // Protect original result from being masked by close() failure
            }

            return $result;
        } catch (Throwable $e) {
            $exceptionId = 'e-' . spl_object_hash($e);
            $errorContext = $this->factory->createErrorContext($e, $exceptionId, $openContext);

            try {
                $this->logger->close($errorContext, $openId);
            } catch (Throwable) {
                // Protect original exception from being masked by close() failure
            }

            throw $e;
        } finally {
            // Persist logs once, regardless of success or failure
            $this->devLogger->log($this->logger);
        }
    }
}
