<?php

namespace BEAR\Resource;

class FakeScalar extends ResourceObject
{
    public function onGet()
    {
        $this->body = 'abc';
    }
}
