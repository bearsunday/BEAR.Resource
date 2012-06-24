<?php

namespace BEAR\Resource;

class ErrorRenderer implements Renderable
{
    public function render(Object $resourceObject)
    {
        throw new \ErrorException;
    }
}
