<?php

namespace BEAR\Resource\Adapter;

use BEAR\Resource\ResourceObject;

class Test extends ResourceObject implements AdapterInterface
{
    public function __construct()
    {
    }

    public function onGet($a, $b)
    {
        $this['posts'] = [$a, $b];

        return $this;
    }
}
