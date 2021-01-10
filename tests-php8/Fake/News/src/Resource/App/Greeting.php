<?php

declare(strict_types=1);

namespace FakeVendor\News\Resource\App;

use BEAR\Resource\Annotation\ResourceParam;
use BEAR\Resource\ResourceObject;

class Greeting extends ResourceObject
{
    /**
     * ResourceParam(param="name", uri="app://self/rparam/login#login_id")
     */
    public function onGet(#[ResourceParam(uri: 'app://self/login#login_id')] string $name = null): static
    {
        $this->body['name'] = $name;

        return $this;
    }

    /**
     * ResourceParam(param="id", uri="app://self/rparam/login{?name}#nickname", templated=true)
     */
    public function onPost(#[ResourceParam(uri: 'app://self/login{?name}#nickname')] string $id, string $name): static
    {
        $this['id'] = $id;

        return $this;
    }
}
