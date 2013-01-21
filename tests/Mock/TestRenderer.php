<?php

namespace BEAR\Resource;

class TestRenderer implements RenderInterface
{
    public function render(AbstractObject $resourceObject)
    {
        return json_encode($resourceObject->body);
    }
}
