<?php

namespace BEAR\Resource\Adapter;

use BEAR\Resource\ResourceObject;

class Nop implements AdapterInterface
{
    public function get($uri)
    {
        return new NopResource;
    }
}
