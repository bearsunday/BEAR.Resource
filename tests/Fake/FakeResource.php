<?php

declare(strict_types=1);

namespace BEAR\Resource;

class FakeResource extends ResourceObject
{
    public function __construct()
    {
        $this->uri = new NullUri();
    }

    public function onGet($a, $b)
    {
        $this['posts'] = [$a, $b];

        return $this;
    }

    public function onPut()
    {
    }
}
