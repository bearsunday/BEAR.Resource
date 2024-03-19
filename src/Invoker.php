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
        return $request->resourceObject->_invokeRequest($this->classInvoker, $request);
    }
}
