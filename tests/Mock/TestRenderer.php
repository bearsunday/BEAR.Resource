<?php

namespace BEAR\Resource;

class TestRenderer implements Renderable
{
    public function render(Object $resourceObject)
    {
        return json_encode($resourceObject->body);
    }
}
