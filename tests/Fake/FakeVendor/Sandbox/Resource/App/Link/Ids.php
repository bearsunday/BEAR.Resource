<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Link;

use BEAR\Resource\ResourceObject;

class Ids extends ResourceObject
{
    public function onGet(): static
    {
        $this->body = ['1'];

        return $this;
    }
}
