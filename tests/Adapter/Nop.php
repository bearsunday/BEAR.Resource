<?php

namespace BEAR\Resource\Adapter;

class Nop implements AdapterInterface
{
    public function get($uri)
    {
        return new NopResource;
    }
}
