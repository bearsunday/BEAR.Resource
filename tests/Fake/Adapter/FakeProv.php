<?php

namespace BEAR\Resource\Adapter;

use BEAR\Resource\ProviderInterface;

class FakeProv implements ProviderInterface, AdapterInterface
{
    public function __construct()
    {
    }

    public function get($path)
    {
        return new \StdClass;
    }
}
