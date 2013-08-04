<?php

namespace BEAR\Resource\Adapter;

use BEAR\Resource\ObjectInterface;
use BEAR\Resource\ProviderInterface;

class Prov implements ObjectInterface, ProviderInterface, AdapterInterface
{
    public function __construct()
    {
    }

    public function get($path)
    {
        return new \StdClass;
    }
}
