<?php

namespace FakeVendor\Sandbox\Resource\App\Href;

use BEAR\Resource\ResourceObject;

class Target extends ResourceObject
{
    public function onGet($id)
    {
        $this['id'] = $id;

        return $this;
    }
}
