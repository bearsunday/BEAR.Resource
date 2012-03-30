<?php

namespace BEAR\Resource\Adapter;

use BEAR\Resource\AbstractObject;

use BEAR\Resource\Object as ResourceObject;

class Test extends AbstractObject implements ResourceObject
{
    public function __construct()
    {}

    public function onGet($a, $b)
    {
        $this['posts'] = [$a, $b];
        return $this;
    }
}
