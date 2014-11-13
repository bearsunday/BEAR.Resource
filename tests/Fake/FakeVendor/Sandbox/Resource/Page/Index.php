<?php

namespace FakeVendor\Sandbox\Resource\Page;

use BEAR\Resource\ResourceObject;

class Index extends ResourceObject
{
    public function onGet($id = 0)
    {
        return $id;
    }
}
