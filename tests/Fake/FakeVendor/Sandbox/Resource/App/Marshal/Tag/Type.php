<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App\Marshal\Tag;

use BEAR\Resource\ResourceObject;

class Type extends ResourceObject
{
    public function onGet($tag_type)
    {
        $this->body = ['type' . $tag_type];

        return $this;
    }
}
