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
 * SemanticInvoker for BEAR.Resource with structured logging
 *
 * Wraps resource requests with semantic logging using open/close lifecycle.
 * Currently accumulates logs via SemanticLogger, retrieved only via flush() in tests.
 *
 * TODO: Consider adding immediate log persistence when close() is called
 * for MCP server integration and AI-assisted debugging.
 */
final class SemanticInvoker implements InvokerInterface
{
    public function __construct(
        #[Named('original')]
        private InvokerInterface $invoker,
        private SemanticLoggerInterface $logger,
        private ContextFactoryInterface $factory,
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

            return $result;
        } catch (Throwable $e) {
            $errorContext = $this->factory->createErrorContext($e);

            $this->logger->close($errorContext, $openId);

            throw $e;
        }
    }
}
