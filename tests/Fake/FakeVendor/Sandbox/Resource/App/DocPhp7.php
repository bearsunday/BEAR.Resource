<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App;

use BEAR\Resource\Annotation\ResourceParam;
use BEAR\Resource\ResourceObject;
use Ray\Di\Di\Assisted;
use Ray\WebContextParam\Annotation\ServerParam;

class DocPhp7 extends ResourceObject
{
    /**
     * @param int    $id          Id
     * @param string $name        Name
     * @param bool   $sw          Swithc
     * @param string $login_id    Login ID
     * @param string $defaultNull DefaultNull
     */
    public function onGet(int $id, string $name, bool $sw, #[ResourceParam(uri: "app://self/login#id")] string $login_id, array $arr, #[Assisted] string $time, $defaultNull = null)
    {
        return $this;
    }

    public function onPost(#[ServerParam(key: "id_key")] int $id)
    {
        return $this;
    }
}
