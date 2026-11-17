<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Link\Jump;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\Code;
use BEAR\Resource\ResourceObject;

class First extends ResourceObject
{
    #[Link(rel: 'second', href: 'app://self/link/jump/second?id={id}', crawl: 'next')]
    public function onGet(int $id): static
    {
        $this->code = Code::ACCEPTED;
        $this->headers = ['ETag' => 'first'];
        $this->body = ['id' => $id, 'name' => 'first'];
        $this->view = 'first view';

        return $this;
    }
}
