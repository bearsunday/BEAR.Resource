<?php

namespace BEAR\Resource\Renderer;

use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;

class FakeTestRenderer implements RenderInterface
{
    public function render(ResourceObject $resourceObject)
    {
        return json_encode($resourceObject->body);
    }
}
