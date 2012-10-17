<?php

namespace BEAR\Resource\Adapter;

use BEAR\Resource\AbstractObject;
use BEAR\Resource\Object as ResourceObject;

class Nop extends AbstractObject implements ResourceObject, Adapter
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
