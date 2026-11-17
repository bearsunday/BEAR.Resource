<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Link\Jump;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\Code;
use BEAR\Resource\ResourceObject;

class Soft400 extends ResourceObject
{
    #[Link(rel: 'second', href: 'app://self/link/jump/second?id={id}')]
    public function onGet(int $id): static
    {
        $this->code = Code::BAD_REQUEST;
        $this->body = ['id' => $id, 'name' => 'soft400'];

        return $this;
    }
}
