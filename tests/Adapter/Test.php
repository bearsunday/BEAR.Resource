<?php

namespace BEAR\Resource\Adapter;

use BEAR\Resource\Renderer\TestRenderer;

class Test implements AdapterInterface
{
    public function get($uri)
    {
        $resource = new FakeResource;
        $resource->setRenderer(new TestRenderer);

        return $resource;
    }
}
