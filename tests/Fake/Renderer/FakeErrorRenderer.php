<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\Renderer;

use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;

class FakeErrorRenderer implements RenderInterface
{
    public function render(ResourceObject $ro)
    {
        throw new \ErrorException;
    }
}
