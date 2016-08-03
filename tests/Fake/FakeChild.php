<?php

namespace BEAR\Resource;

class FakeChild extends ResourceObject
{
    public function onGet()
    {
        $this['tree'] = 3;

        return $this;
    }
}
