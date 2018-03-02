<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
