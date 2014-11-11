<?php

namespace FakeVendor\Sandbox\Resource\App\Bird;

use BEAR\Resource\ResourceObject;

class Canary extends ResourceObject
{
    public $links = [
        'friend' => 'app://self/bird/friend'
    ];

    public $body = [
        'name' => 'chill kun'
    ];

    public function onGet()
    {
        return $this;
    }
}
