<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\MethodNotAllowedException;
use Ray\Di\Di\Named;

use function get_class;

final class ExtraMethodInvoker
{
    /** @Named("optionsRenderer=options") */
    #[Named('optionsRenderer=options')]
    public function __construct(private RenderInterface $optionsRenderer)
    {
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
