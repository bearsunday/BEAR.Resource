<?php

namespace FakeVendor\Sandbox\Resource\Foo\User;

use BEAR\Resource\ResourceObject;

class Profile extends ResourceObject
{
    public function onGet($id)
    {
    }

    public function onPut($id, $name, $age = 17)
    {
    }
}
