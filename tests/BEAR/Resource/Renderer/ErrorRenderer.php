<?php

namespace BEAR\Resource\Renderer;

use BEAR\Resource\RenderInterface;
use BEAR\Resource\AbstractObject;

class ErrorRenderer implements RenderInterface
{
    public function render(AbstractObject $resourceObject)
    {
        throw new \ErrorException;
    }
}
