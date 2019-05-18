<?php

declare(strict_types=1);

namespace BEAR\Resource;

use function is_callable;

final class Invoker implements InvokerInterface
{
    /**
     * @var NamedParameterInterface
     */
    private $params;

    /**
     * @var ExtraMethodInvoker
     */
    private $extraMethod;

    public function __construct(NamedParameterInterface $params, ExtraMethodInvoker $extraMethod)
    {
        $this->params = $params;
        $this->extraMethod = $extraMethod;
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(AbstractRequest $request) : ResourceObject
    {
        $callable = [$request->resourceObject, 'on' . ucfirst($request->method)];
        if (! is_callable($callable)) {
            // OPTIONS or HEAD
            return ($this->extraMethod)($request, $this);
        }
        $params = $this->params->getParameters($callable, $request->query);
        $response = call_user_func_array($callable, $params);
        if (! $response instanceof ResourceObject) {
            $request->resourceObject->body = $response;
            $response = $request->resourceObject;
        }

        return $response;
    }
}
