<?php

namespace FakeVendor\Sandbox\Resource\App;

use BEAR\Resource\Annotation\ResourceParam;
use BEAR\Resource\ResourceObject;
use Ray\Di\Di\Assisted;

class DocPhp7 extends ResourceObject
{
    /**
     * @param int    $id          Id
     * @param string $name        Name
     * @param bool   $sw          Swithc
     * @param string $login_id    Login ID
     * @param array  $arr
     * @param string $time
     * @param string $defaultNull DefaultNull
     *
     * @ResourceParam(param="login_id", uri="app://self/login#id")
     * @Assisted({"time"})
     */
    public function onGet(int $id, string $name, bool $sw, string $login_id, array $arr, string $time, $defaultNull = null)
    {
        return $this;
    }
}
