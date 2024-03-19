<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\MethodNotAllowedException;
use Ray\Di\Di\Named;

final class ExtraMethodInvoker
{
    public function __construct(
        #[Named('options')]
        private readonly RenderInterface $optionsRenderer,
    ) {
    }

    public function __invoke(AbstractRequest $request, InvokerInterface $invoker): ResourceObject
    {
        if ($request->method === Request::OPTIONS) {
            $ro = $request->resourceObject;
            $ro->view = $this->optionsRenderer->render($request->resourceObject);

            return $ro;
        }

        if ($request->method === Request::HEAD) {
            $getRequest = clone $request;
            $getRequest->method = 'get';
            $ro = $invoker->invoke($getRequest);
            $ro->body = null;

            return $ro;
        }

        throw new MethodNotAllowedException($request->resourceObject::class . "::{({$request->method}}()", 405);
    }
}
