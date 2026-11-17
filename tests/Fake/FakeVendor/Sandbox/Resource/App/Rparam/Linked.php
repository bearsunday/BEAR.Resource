<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Rparam;

use BEAR\Resource\Annotation\ResourceParam;
use BEAR\Resource\ResourceObject;

class Linked extends ResourceObject
{
    #[ResourceParam(uri: 'app://self/rparam/login#link=self:profile', param: 'name')]
    public function onGet(?string $name = null)
    {
        $this->body = ['name' => $name];

        return $this;
    }
}
