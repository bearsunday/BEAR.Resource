<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Rparam;

use BEAR\Resource\ResourceObject;

class Login extends ResourceObject
{
    public function onGet($name = 'sunday')
    {
        $this['login_id'] = 'LOGINID';
        $this['nickname'] = 'login:' . $name;

        return $this;
    }
}
