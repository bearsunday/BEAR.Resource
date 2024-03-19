<?php
// phpcs:ignoreFile

namespace BEAR\Resource;

final class Invoker implements InvokerInterface
{
    public function __construct(
        private PhpClassInvoker $classInvoker
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(AbstractRequest $request): ResourceObject
    {
        if ($request->resourceObject instanceof HttpResourceObject) {
            return $request->resourceObject->request($request);
        }
        return $this->classInvoker->invoke($request);
    }
}
