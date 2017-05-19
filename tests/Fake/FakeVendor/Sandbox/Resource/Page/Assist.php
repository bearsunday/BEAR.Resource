<?php

namespace FakeVendor\Sandbox\Resource\Page;

use BEAR\Resource\ResourceObject;
use Ray\Di\Di\Assisted;
use Ray\Di\Di\Named;

class Assist extends ResourceObject
{
    /**
     * @Assisted({"login_id"})
     * @Named("login_id=login_id")
     */
    public function onGet($login_id = null)
    {
        return 'login_id:' . $login_id;
    }
}
