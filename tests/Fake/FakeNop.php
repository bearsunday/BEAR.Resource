<?php

namespace BEAR\Resource;

class FakeNop implements AdapterInterface
{
    public function get(AbstractUri $uri)
    {
        return new FakeNopResource;
    }
}
