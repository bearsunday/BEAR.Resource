<?php

namespace BEAR\Resource\Adapter;

use BEAR\Resource\AbstractUri;

class FakeProv implements AdapterInterface
{
    public function get(AbstractUri $uri)
    {
        return new \StdClass;
    }
}
