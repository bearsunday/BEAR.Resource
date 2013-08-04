<?php

namespace BEAR\Resource\Adapter;

use BEAR\Resource\AbstractObject;
use BEAR\Resource\ObjectInterface as ResourceObject;

class Nop extends AbstractObject implements ResourceObject, AdapterInterface
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
