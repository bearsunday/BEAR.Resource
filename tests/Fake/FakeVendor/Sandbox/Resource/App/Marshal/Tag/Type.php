<?php
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
