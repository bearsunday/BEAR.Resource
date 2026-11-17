<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Link;

use BEAR\Resource\ResourceObject;

class LinkValue extends ResourceObject
{
    public function onGet(): static
    {
        $this->body = ['#top'];

        return $this;
    }
}
