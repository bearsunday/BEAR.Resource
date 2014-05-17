<?php

namespace TestVendor\Sandbox\Resource\App\Bird;

use BEAR\Resource\ResourceObject;

class Canary extends ResourceObject
{
    public $body = [
        'name' => 'chill kun'
    ];

    public function onGet()
    {
        return $this;
    }
}
