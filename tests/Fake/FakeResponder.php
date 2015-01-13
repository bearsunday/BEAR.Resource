<?php

namespace BEAR\Resource;

class FakeResponder implements TransferInterface
{
    public $class;

    /**
     * {@inheritdoc}
     */
    public function __invoke(ResourceObject $resourceObject, array $server)
    {
        // transfer resource object to external boundary (HTTP / File ...)
        $this->class = get_class($resourceObject);
    }
}
