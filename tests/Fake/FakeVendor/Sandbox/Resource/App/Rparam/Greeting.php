<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App\Rparam;

use BEAR\Resource\Annotation\ResourceParam;
use BEAR\Resource\ResourceObject;

class Greeting extends ResourceObject
{
    /**
     * @ResourceParam(param="name", uri="app://self/rparam/login#login_id")
     */
    public function onGet($name = null)
    {
        $this['name'] = $name;

        return $this;
    }

    public function onPut($name)
    {
    }

    /**
     * @ResourceParam(param="id", uri="app://self/rparam/login{?name}#nickname", templated=true)
     */
    public function onPost($id, $name)
    {
        $this['id'] = $id;

        return $this;
    }
}
