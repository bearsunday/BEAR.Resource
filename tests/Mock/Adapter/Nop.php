<?php

namespace BEAR\Resource\Adapter;

use BEAR\Resource\Object as ResourceObject;

class Nop implements ResourceObject
{
    public function __construct()
    {}

    public function __call($name, $args)
    {
        return array($name, $args);
    }
}