<?php

namespace FakeVendor\Sandbox\Resource\Page;

use BEAR\Resource\ResourceObject;

class FakeLoopItem extends ResourceObject
{
    public function onGet(string $num): ResourceObject
    {
        $this->body = [
            'num' => $num
        ];

        return $this;
    }
}
