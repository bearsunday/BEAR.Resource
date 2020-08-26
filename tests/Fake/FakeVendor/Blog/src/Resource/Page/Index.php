<?php

declare(strict_types=1);

namespace FakeVendor\Blog\Resource\Page;

use BEAR\Resource\ResourceObject;

class Index extends ResourceObject
{
    public function onGet($id = 0)
    {
        return $id;
    }
}
