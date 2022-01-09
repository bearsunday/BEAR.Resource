<?php
// phpcs:ignoreFile

namespace BEAR\Resource;

use function call_user_func_array;
use function is_callable;
use function ucfirst;

final class Invoker implements InvokerInterface
{
    private NamedParameterInterface $params;
    private ExtraMethodInvoker $extraMethod;
    private LoggerInterface $logger;

    public function __construct(NamedParameterInterface $params, ExtraMethodInvoker $extraMethod, LoggerInterface $logger)
    {
        $this->params = $params;
        $this->extraMethod = $extraMethod;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(AbstractRequest $request): ResourceObject
    {
        if ($request->resourceObject instanceof HttpResourceObject) {
            return $request->resourceObject->request($request);
        }

        $callable = [$request->resourceObject, 'on' . ucfirst($request->method)];
        if (! is_callable($callable)) {
            // OPTIONS or HEAD
            return ($this->extraMethod)($request, $this);
        }

        $params = $this->params->getParameters($callable, $request->query);
        /** @psalm-suppress MixedAssignment */
        $response = call_user_func_array($callable, $params);
        if (! $response instanceof ResourceObject) {
            $request->resourceObject->body = $response;
            $response = $request->resourceObject;
        }

        ($this->logger)($response);

        return $response;
    }
}
