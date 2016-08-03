<?php

namespace BEAR\Resource;

class FakeNop implements AdapterInterface
{
    public function get(AbstractUri $uri)
    {
        unset($uri);
        return new FakeNopResource;
    }
}
