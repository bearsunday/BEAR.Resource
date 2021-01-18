<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Rparam;

use BEAR\Resource\Annotation\ResourceParam;
use BEAR\Resource\ResourceObject;

class Greeting extends ResourceObject
{
    /**
     * @ResourceParam(param="name", uri="app://self/rparam/login#login_id")
     */
    #[ResourceParam(param: "name", uri: "app://self/rparam/login#login_id")]
    public function onGet(string $name = null)
    {
        $this['name'] = $name;

        return $this;
    }

    public function onPut(string $name)
    {
    }

    /**
     * @ResourceParam(param="id", uri="app://self/rparam/login{?name}#nickname", templated=true)
     */
    #[ResourceParam(param: "id", uri: "app://self/rparam/login{?name}#nickname", templated: true)]
    public function onPost(string $id, string $name)
    {
        $this['id'] = $id;

        return $this;
    }
}
