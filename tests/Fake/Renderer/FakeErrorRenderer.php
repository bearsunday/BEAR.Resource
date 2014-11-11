<?php

namespace BEAR\Resource\Renderer;

use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;

class FakeErrorRenderer implements RenderInterface
{
    public function render(ResourceObject $resourceObject)
    {
        throw new \ErrorException;
    }
}
