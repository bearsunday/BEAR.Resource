<?php

declare(strict_types=1);

namespace BEAR\Resource;

use FakeVendor\Sandbox\Resource\App\Class\UnresolvableObject;

class TestResourceUnresolvable extends ResourceObject
{
    public function onGet(UnresolvableObject $obj): static
    {
        $this->body = [];

        return $this;
    }
}
