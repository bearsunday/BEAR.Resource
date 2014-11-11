<?php

namespace BEAR\Resource;

class FakeResource extends ResourceObject
{
    public $uri = 'test://self/path/to/resource';

    public function onGet($a, $b)
    {
        $this['posts'] = [$a, $b];

        return $this;
    }

    public function onPut()
    {
    }
}
