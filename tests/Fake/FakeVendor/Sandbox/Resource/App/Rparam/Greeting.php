<?php

namespace FakeVendor\Sandbox\Resource\App\Rparam;

use BEAR\Resource\Annotation\ResourceParam;
use BEAR\Resource\ResourceObject;

class Greeting extends ResourceObject
{
    /**
     * @ResourceParam(param="name", uri="app://self/rparam/login#nickname")
     */
    public function onGet($name)
    {
        $this['name'] = $name;

        return $this;
    }
}
