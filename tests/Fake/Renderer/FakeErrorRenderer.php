<?php

declare(strict_types=1);

namespace BEAR\Resource\Renderer;

use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;

class FakeErrorRenderer implements RenderInterface
{
    public function render(ResourceObject $ro)
    {
        throw new \ErrorException();
    }
}
