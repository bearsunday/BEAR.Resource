<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App;

use BEAR\Resource\ResourceObject;

class Stone extends ResourceObject
{
    public function onGet(int $id)
    {
        unset($id);

        return $this;
    }
}
