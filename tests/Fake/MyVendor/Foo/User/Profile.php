<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
