<?php

namespace BEAR\Resource;

class FakeResponder implements TransferInterface
{
    public $class;

    public function __invoke(ResourceObject $resourceObject)
    {
        // transfer resource object to external boundary (HTTP / File ...)
        $this->class = get_class($resourceObject);
    }
}
