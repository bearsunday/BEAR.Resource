<?php

namespace BEAR\Resource\Adapter;

class FakeNop implements AdapterInterface
{
    public function get($uri)
    {
        return new FakeNopResource;
    }
}
