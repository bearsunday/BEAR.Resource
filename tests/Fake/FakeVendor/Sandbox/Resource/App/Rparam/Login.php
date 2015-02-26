<?php

namespace FakeVendor\Sandbox\Resource\App\Rparam;

use BEAR\Resource\ResourceObject;

class Login extends ResourceObject
{
    public function onGet()
    {
        $this['nickname'] = 'sunday';

        return $this;
    }
}
