<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Rparam;

use BEAR\Resource\Annotation\ResourceParam;
use BEAR\Resource\ResourceObject;
use Ray\Di\Di\Assisted;

class Greeting extends ResourceObject
{
    /**
     * ResourceParam annotated class
     *
     * This is not an intentional attribute to test annotations.
     *
     * @ResourceParam(uri="app://self/rparam/login#login_id", param="name")
     * @Assisted({"assisted"})
     */
    public function onGet(string $name = null, string $assisted = null)
    {
        $this['name'] = $name;

        return $this;
    }

    public function onPut(string $name)
    {
    }

    /**
     * @ResourceParam(uri="app://self/rparam/login{?name}#nickname", templated=true, param="id")
     */
    public function onPost(string $id, string $name)
    {
        $this['id'] = $id;

        return $this;
    }
}
