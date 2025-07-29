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

use function array_merge;
use function get_class;
use function is_array;
use function property_exists;
use function strtolower;
use function ucfirst;

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
        // Use resource class name for better traceability
        $resourceClass = get_class($request->resourceObject);

        // Convert HTTP method to resource method (GET -> onGet)
        $resourceMethod = 'on' . ucfirst(strtolower($request->method));

        // Combine query and body parameters for complete parameter context
        $parameters = array_merge(
            $request->query,
            property_exists($request, 'body') && is_array($request->body) ? $request->body : [],
        );

        /** @var array<string, mixed> $parameters */
        $context = new ResourceOpenContext(
            $resourceClass,
            $resourceMethod,
            $parameters,
        );

        $openId = $this->semanticLogger->open($context);

        try {
            $result = $this->invoker->invoke($request);

            /** @var array<string, mixed> $body */
            $body = (array) $result->body;
            $closeContext = new ResourceCompleteContext(
                $resourceClass,
                $resourceMethod,
                $result->code,
                $body,
            );

            $this->semanticLogger->close($closeContext, $openId);

            return $result;
        } catch (Throwable $e) {
            $errorContext = new ResourceErrorContext(
                $resourceClass,
                $resourceMethod,
                $e::class,
                $e->getMessage(),
            );

            $this->semanticLogger->close($errorContext, $openId);

            throw $e;
        }
    }
}
