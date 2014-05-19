<?php

namespace BEAR\Resource\Adapter;

use BEAR\Resource\ResourceObject;

class TestResource extends ResourceObject
{
    public function onGet($a, $b)
    {
        $this['posts'] = [$a, $b];

        return $this;
    }

    public function onPut()
    {
    }
}
