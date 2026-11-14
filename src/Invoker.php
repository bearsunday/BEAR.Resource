<?php
// phpcs:ignoreFile

namespace BEAR\Resource;

final readonly class Invoker implements InvokerInterface
{
    /** @psalm-pure */
    public function __construct(
        private PhpClassInvoker $classInvoker
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-suppress PossiblyUnusedReturnValue
     */
    #[\Override]
    public function invoke(AbstractRequest $request): ResourceObject
    {
        return $request->resourceObject->_invokeRequest($this->classInvoker, $request);
    }
}
