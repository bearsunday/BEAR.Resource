<?php

declare(strict_types=1);

namespace BEAR\Resource\Renderer;

use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;

class FakeTestRenderer implements RenderInterface
{
    public function render(ResourceObject $ro)
    {
        return json_encode($ro->body, JSON_THROW_ON_ERROR);
    }
}
