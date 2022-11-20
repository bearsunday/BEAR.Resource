<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Rparam;

use BEAR\Resource\Annotation\ResourceParam;
use BEAR\Resource\ResourceObject;

class Greeting extends ResourceObject
{
    public function onGet(#[ResourceParam(uri: "app://self/rparam/login#login_id")] string $name = null)
    {
        $this['name'] = $name;

        return $this;
    }

    public function onPut(string $name)
    {
    }

    public function onPost(#[ResourceParam(uri: "app://self/rparam/login{?name}#nickname", templated: true)] string $id, string $name)
    {
        $this['id'] = $id;

        return $this;
    }
}
