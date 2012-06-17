<?php

namespace BEAR\Resource;

class TestRenderer implements RenderInterface
{
    public function render(Object $resourceObject)
    {
        return json_encode($resourceObject->body);
    }
}
