<?php

namespace BEAR\Resource\Adapter;

use BEAR\Resource\ResourceObject;

class NopResource extends ResourceObject
{
    public $time;

    public function __construct()
    {
        $this->time = microtime(true);
    }

    public function onGet($a, $b)
    {
        return array($a, $b);
    }
}
