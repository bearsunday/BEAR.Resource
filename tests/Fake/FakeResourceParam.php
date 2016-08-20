<?php

namespace BEAR\Resource;

class FakeResourceParam implements AdapterInterface
{
    public function get(AbstractUri $uri)
    {
        unset($uri);

        return new FakeNopResource;
    }
}
