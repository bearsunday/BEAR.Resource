<?php

namespace BEAR\Resource;

class TestRenderer implements Renderable
{
    public function render(AbstractObject $resourceObject)
    {
        return json_encode($resourceObject->body);
    }
}
