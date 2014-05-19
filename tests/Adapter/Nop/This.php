<?php

namespace BEAR\Resource\Adapter\Nop;

use BEAR\Resource\ResourceObject;

class This extends ResourceObject
{
    public function onGet($a, $b)
    {
        $this->body = [$a, $b];

        return $this;
    }
}
