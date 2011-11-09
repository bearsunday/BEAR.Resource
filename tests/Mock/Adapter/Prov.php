<?php

namespace BEAR\Resource\Adapter;

use BEAR\Resource\Object,
    BEAR\Resource\Provider;

class Prov implements Object, Provider
{
    public function __construct()
    {}
        
    public function get($path)
    {
        return new \StdClass;
    }
}    