<?php

namespace FakeVendor\Sandbox\Resource\Page;

use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;

class FakeLoop extends ResourceObject
{
    private \BEAR\Resource\ResourceInterface $resource;

    public function __construct(ResourceInterface $resource)
    {
        $this->resource = $resource;
    }

    public function onGet(): ResourceObject
    {
        $request = $this->resource->get->uri('/fake-loop-item');
        foreach (range(1, 5) as $i) {
            $this->body[(string) $i] = $request(['num' => (string) $i]);
        }

        return $this;
    }
}
