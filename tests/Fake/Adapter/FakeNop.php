<?php

namespace BEAR\Resource\Adapter;

use BEAR\Resource\AbstractUri;

class FakeNop implements AdapterInterface
{
    public function get(AbstractUri $uri)
    {
        return new FakeNopResource;
    }
}
