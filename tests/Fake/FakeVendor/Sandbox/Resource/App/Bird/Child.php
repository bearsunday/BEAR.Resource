<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Bird;

use BEAR\Resource\ResourceObject;

class Child extends ResourceObject
{
    public function onGet(string $id)
    {
        $this->body = [
            'id' => $id
        ];

        return $this;
    }
}
