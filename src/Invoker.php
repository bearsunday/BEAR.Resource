<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\MethodNotAllowedException;
use function is_callable;
use Ray\Di\Di\Named;

final class Invoker implements InvokerInterface
{
    /**
     * @var NamedParameterInterface
     */
    private $params;

    /**
     * @var RenderInterface
     */
    private $optionsRenderer;

    /**
     * @Named("optionsRenderer=options")
     */
    public function __construct(NamedParameterInterface $params, RenderInterface $optionsRenderer)
    {
        $this->params = $params;
        $this->optionsRenderer = $optionsRenderer;
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(AbstractRequest $request) : ResourceObject
    {
        $callable = [$request->resourceObject, 'on' . ucfirst($request->method)];
        if (! is_callable($callable)) {
            return $this->noMethod($request);
        }
        $params = $this->params->getParameters($callable, $request->query);
        $response = call_user_func_array($callable, $params);
        if (! $response instanceof ResourceObject) {
            $request->resourceObject->body = $response;
            $response = $request->resourceObject;
        }

        return $response;
    }

    private function noMethod(AbstractRequest $request) : ResourceObject
    {
        if ($request->method === Request::OPTIONS) {
            $ro = $request->resourceObject;
            $ro->view = $this->optionsRenderer->render($request->resourceObject);

            return $ro;
        }

        throw new MethodNotAllowedException(get_class($request->resourceObject) . "::{({$request->method}}()", 405);
    }
}
