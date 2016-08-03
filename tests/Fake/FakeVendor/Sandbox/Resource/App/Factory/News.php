<?php

namespace FakeVendor\Sandbox\Resource\App\Factory;

use BEAR\Resource\ResourceObject as Ro;

class News extends Ro
{
    public function onGet($id)
    {
        return __CLASS__ . $id;
    }
}
