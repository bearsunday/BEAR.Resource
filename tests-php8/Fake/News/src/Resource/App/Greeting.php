<?php

declare(strict_types=1);

namespace FakeVendor\News\Resource\App;

use BEAR\Resource\Annotation\ResourceParam;
use BEAR\Resource\ResourceObject;

class Greeting extends ResourceObject
{
    /**
     * ResourceParam(param="name", uri="app://self/rparam/login#name")
     */
    public function onGet(#[ResourceParam(uri: 'app://self/login#nickname')] string $nickname = null): static
    {
        $this->body['nickname'] = $nickname;

        return $this;
    }

    /**
     * ResourceParam(param="id", uri="app://self/rparam/login{?name}#nickname", templated=true)
     */
    public function onPost(#[ResourceParam(uri: 'app://self/login{?name}#login_id', templated: true)] string $id, string $name = null): static
    {
        $this['id'] = $id;

        return $this;
    }
}
