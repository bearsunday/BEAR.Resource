<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Link\Carry;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class B extends ResourceObject
{
    #[Link(rel: 'c', href: 'app://self/link/carry/c')]
    public function onGet(): static
    {
        $this->body = ['name' => 'b'];

        return $this;
    }
}
