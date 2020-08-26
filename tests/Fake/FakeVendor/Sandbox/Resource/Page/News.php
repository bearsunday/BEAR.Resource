<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\Page;

use BEAR\Resource\ResourceObject;

class News extends ResourceObject
{
    public function onGet(string $id)
    {
        return __CLASS__ . $id;
    }
}
