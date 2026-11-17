<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Link\Jump;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\Code;
use BEAR\Resource\ResourceObject;

class Soft404 extends ResourceObject
{
    #[Link(rel: 'second', href: 'app://self/link/jump/second?id={id}')]
    public function onGet(int $id): static
    {
        $this->code = Code::NOT_FOUND;
        $this->body = ['id' => $id, 'name' => 'soft404'];

        return $this;
    }
}
