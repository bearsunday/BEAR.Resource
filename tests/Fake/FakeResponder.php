<?php

declare(strict_types=1);

namespace BEAR\Resource;

class FakeResponder implements TransferInterface
{
    public $class;

    /**
     * {@inheritdoc}
     */
    public function __invoke(ResourceObject $ro, array $server)
    {
        // transfer resource object to external boundary (HTTP / File ...)
        unset($server);
        $this->class = $ro::class;
    }
}
