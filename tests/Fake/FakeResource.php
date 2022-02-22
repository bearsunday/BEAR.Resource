<?php

declare(strict_types=1);

namespace BEAR\Resource;

class FakeResource extends ResourceObject
{
    public $body = [];

    public function __construct()
    {
        $this->uri = new NullUri();
    }

    public function onGet($a, $b)
    {
        $this['posts'] = [$a, $b];
        $this['nullValue'] = null;

        return $this;
    }

    public function onPut()
    {
    }
}
