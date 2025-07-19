<?php

declare(strict_types=1);

namespace BEAR\SchemaLogger;

use BEAR\Resource\AbstractRequest;
use BEAR\Resource\Code;
use BEAR\Resource\Exception\BadRequestException;
use BEAR\Resource\ExtraMethodInvoker;
use BEAR\Resource\InvokerInterface;
use BEAR\Resource\LoggerInterface;
use BEAR\Resource\NamedParameterInterface;
use BEAR\Resource\ResourceObject;
use Koriym\SchemaLogger\SchemaLogEntry;
use Koriym\SchemaLogger\SchemaLoggerInterface;
use Override;
use Throwable;

use function call_user_func_array;
use function is_callable;
use function microtime;
use function ucfirst;

final class ResourceInvokerAdapter implements InvokerInterface
{
    public function __construct(
        private readonly NamedParameterInterface $params,
        private readonly ExtraMethodInvoker $extraMethod,
        private readonly LoggerInterface $logger,
        private readonly SchemaLoggerInterface $schemaLogger,
    ) {
    }

    #[Override]
    public function invoke(AbstractRequest $request): ResourceObject
    {
        $sessionId = $this->schemaLogger->generateSessionId();
        $context = new BearSchemaLogContext(
            BearSchemaLogContext::RESOURCE_REQUEST,
            [
                'uri' => (string) $request->resourceObject->uri,
                'method' => $request->method,
                'query' => $request->query,
                'type' => 'resource_request',
            ]
        );
        $this->schemaLogger->open($context);
        
        $startTime = microtime(true);
        
        $startEntry = new SchemaLogEntry('resource_invoke_start', $sessionId . '_start');
        $this->schemaLogger->add($startEntry);

        $callable = [$request->resourceObject, 'on' . ucfirst($request->method)];
        if (! is_callable($callable)) {
            // OPTIONS or HEAD
            $response = ($this->extraMethod)($request, $this);
            $extraEntry = new SchemaLogEntry('extra_method_invoked', $sessionId . '_extra');
            $this->schemaLogger->add($extraEntry);
            ($this->logger)($response);
            $this->schemaLogger->close();
            return $response;
        }

        $params = $this->params->getParameters($callable, $request->query);
        
        try {
            $response = call_user_func_array($callable, $params);
        } catch (Throwable $e) {
            $errorEntry = new SchemaLogEntry('error', $sessionId . '_error');
            $this->schemaLogger->add($errorEntry);
            $this->schemaLogger->close();
            
            if ($e::class === \TypeError::class) {
                throw new BadRequestException('Invalid parameter type', Code::BAD_REQUEST, $e);
            }
            throw $e;
        }
        
        if (! $response instanceof ResourceObject) {
            $request->resourceObject->body = $response;
            $response = $request->resourceObject;
        }

        $endEntry = new SchemaLogEntry('resource_invoke_end', $sessionId . '_end');
        $this->schemaLogger->add($endEntry);

        ($this->logger)($response);
        $this->schemaLogger->close();

        return $response;
    }
}