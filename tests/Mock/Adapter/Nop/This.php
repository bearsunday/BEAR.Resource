<?php

namespace BEAR\Resource\Adapter\Nop;

use BEAR\Resource\AbstractObject;
use BEAR\Resource\Object as ResourceObject;

class This extends AbstractObject implements ResourceObject, Adapter
{
    public function onGet($a, $b)
    {
        $this->body = [$a, $b];
        return $this;
    }
}
