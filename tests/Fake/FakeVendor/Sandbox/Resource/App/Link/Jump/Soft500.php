<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Link\Jump;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\Code;
use BEAR\Resource\ResourceObject;

class Soft500 extends ResourceObject
{
    #[Link(rel: 'first', href: 'app://self/link/jump/first?id={id}')]
    public function onGet(int $id): static
    {
        $this->code = Code::ERROR;
        $this->body = ['id' => $id, 'name' => 'soft500'];

        return $this;
    }
}
