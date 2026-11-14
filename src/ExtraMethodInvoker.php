<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\MethodNotAllowedException;
use Ray\Di\Di\Named;

final readonly class ExtraMethodInvoker
{
    /** @psalm-pure */
    public function __construct(
        #[Named('options')]
        private RenderInterface $optionsRenderer,
    ) {
    }

    public function __invoke(AbstractRequest $request, InvokerInterface $invoker): ResourceObject
    {
        if ($request->method === Method::OPTIONS) {
            $ro = $request->resourceObject;
            $ro->view = $this->optionsRenderer->render($request->resourceObject);

            return $ro;
        }

        if ($request->method === Method::HEAD) {
            $getRequest = clone $request;
            $getRequest->method = Method::GET;
            $ro = $invoker->invoke($getRequest);
            $ro->body = null;

            return $ro;
        }

        throw new MethodNotAllowedException($request->resourceObject::class . "::{({$request->method->value}}()", 405);
    }
}
