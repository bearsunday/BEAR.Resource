<?php

namespace BEAR\Resource\Renderer;

use BEAR\Resource\RenderInterface;
use BEAR\Resource\AbstractObject;

class TestRenderer implements RenderInterface
{
    public function render(AbstractObject $resourceObject)
    {
        return json_encode($resourceObject->body);
    }
}
