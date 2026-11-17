<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Link\Carry;

use BEAR\Resource\ResourceObject;

class C extends ResourceObject
{
    public function onGet(): static
    {
        $this->headers = ['Surrogate-Key' => 'c'];
        $this->body = ['name' => 'c'];

        return $this;
    }
}
