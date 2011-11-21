<?php

namespace BEAR\Resource\Adapter;

use BEAR\Resource\Object as ResourceObject;

class Nop implements ResourceObject
{
    public function __construct()
    {}

    public function onGet($a, $b)
    {
        return array($a, $b);
    }
}