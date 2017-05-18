<?php

namespace FakeVendor\Sandbox\Resource\App;

use BEAR\Resource\ResourceObject;

class DocPhp7 extends ResourceObject
{
    public function onGet(int $id, string $name, bool $sw, array $arr, $defaultNull = null)
    {
        return $this;
    }
}
