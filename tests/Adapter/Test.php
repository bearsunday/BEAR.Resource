<?php

namespace BEAR\Resource\Adapter;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Renderer\TestRenderer;

class Test implements AdapterInterface
{
    public function get($uri)
    {
        $resource = new TestResource;
        $resource->setRenderer(new TestRenderer);

        return $resource;
    }
}