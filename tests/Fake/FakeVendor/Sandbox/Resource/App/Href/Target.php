<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Href;

use BEAR\Resource\ResourceObject;

class Target extends ResourceObject
{
    public function onGet(int $id)
    {
        $this['id'] = $id;

        return $this;
    }
}
