<?php

namespace BEAR\Resource;

class ErrorRenderer implements RenderInterface
{
    public function render(AbstractObject $resourceObject)
    {
        throw new \ErrorException;
    }
}
