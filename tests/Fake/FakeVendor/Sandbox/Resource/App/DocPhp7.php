<?php

namespace FakeVendor\Sandbox\Resource\App;

use BEAR\Resource\ResourceObject;

class DocPhp7 extends ResourceObject
{
    /**
     * @param int    $id          Id
     * @param string $name        Name
     * @param bool   $sw          Swithc
     * @param array  $arr
     * @param string $defaultNull DefaultNull
     *
     * @return $this
     */
    public function onGet(int $id, string $name, bool $sw, array $arr, $defaultNull = null)
    {
        return $this;
    }
}
