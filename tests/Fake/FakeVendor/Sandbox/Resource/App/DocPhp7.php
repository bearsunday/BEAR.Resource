<?php

namespace FakeVendor\Sandbox\Resource\App;

use BEAR\Resource\ResourceObject;

class DocPhp7 extends ResourceObject
{
    /**
     * @param int    $id
     * @param string $name
     * @param bool   $sw
     * @param array  $arr
     * @param string $defaultNull
     *
     * @return $this
     */
    public function onGet(int $id, string $name, bool $sw, array $arr, $defaultNull = null)
    {
        return $this;
    }
}
