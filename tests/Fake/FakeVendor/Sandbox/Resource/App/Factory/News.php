<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Factory;

use BEAR\Resource\ResourceObject as Ro;

class News extends Ro
{
    public function onGet(int $id)
    {
        return __CLASS__ . $id;
    }
}
