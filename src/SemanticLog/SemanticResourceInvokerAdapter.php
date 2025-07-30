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

use function strtoupper;

final class SemanticResourceInvokerAdapter implements InvokerInterface
{
    public function __construct(
        #[Named('Original')]
        private InvokerInterface $invoker,
        private SemanticLoggerInterface $semanticLogger,
    ) {
    }

    #[Override]
    public function invoke(AbstractRequest $request): ResourceObject
    {
        // Use URI and method for better user understanding
        $uri = $request->toUri();
        $method = strtoupper($request->method);

        $query = $request->query;

        $context = new ResourceOpenContext(
            $uri,
            $method,
            $query,
        );

        $openId = $this->semanticLogger->open($context);

        try {
            $result = $this->invoker->invoke($request);

            $closeContext = new ResourceCompleteContext(
                $result,
                $method,
            );

            $this->semanticLogger->close($closeContext, $openId);

            return $result;
        } catch (Throwable $e) {
            $errorContext = new ResourceErrorContext(
                $uri,
                $method,
                $e::class,
                $e->getMessage(),
            );

            $this->semanticLogger->close($errorContext, $openId);

            throw $e;
        }
    }
}
