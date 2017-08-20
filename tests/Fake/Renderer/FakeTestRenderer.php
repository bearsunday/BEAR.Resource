<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\Renderer;

use BEAR\Resource\RenderInterface;
use BEAR\Resource\ResourceObject;

class FakeTestRenderer implements RenderInterface
{
    public function render(ResourceObject $resourceObject)
    {
        return json_encode($resourceObject->body);
    }
}
