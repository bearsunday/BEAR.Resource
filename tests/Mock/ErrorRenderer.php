<?php

namespace BEAR\Resource;

class ErrorRenderer implements Renderable
{
    public function render(AbstractObject $resourceObject)
    {
        throw new \ErrorException;
    }
}
