<?php

namespace BEAR\Resource;

class FakeProv implements AdapterInterface
{
    public function get(AbstractUri $uri)
    {
        return new \StdClass;
    }
}
