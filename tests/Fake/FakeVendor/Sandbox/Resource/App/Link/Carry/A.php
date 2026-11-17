<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Link\Carry;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class A extends ResourceObject
{
    #[Link(rel: 'b', href: 'app://self/link/carry/b', crawl: 'tree')]
    #[Link(rel: 'stop', href: 'app://self/link/carry/stop')]
    public function onGet(): static
    {
        $this->headers = ['Surrogate-Key' => 'a'];
        $this->body = ['name' => 'a'];

        return $this;
    }
}
