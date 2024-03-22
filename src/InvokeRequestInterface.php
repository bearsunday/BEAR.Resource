<?php

declare(strict_types=1);

namespace BEAR\Resource;

interface InvokeRequestInterface
{
    /**
     * Invokes a request using the given invoker and request objects.
     *
     * @return ResourceObject The resulting resource object returned from the request invocation.
     */
    public function _invokeRequest(InvokerInterface $invoker, AbstractRequest $request): ResourceObject;
}
