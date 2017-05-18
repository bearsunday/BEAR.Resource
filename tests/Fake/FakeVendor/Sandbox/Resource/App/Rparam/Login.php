<?php

namespace FakeVendor\Sandbox\Resource\App\Rparam;

use BEAR\Resource\ResourceObject;

class Login extends ResourceObject
{
    public function onGet($name = 'sunday')
    {
        $this['nickname'] = 'login:' . $name;

        return $this;
    }
}
