<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Link\Jump;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class Start extends ResourceObject
{
    #[Link(rel: 'first', href: 'app://self/link/jump/first?id={id}', crawl: 'tree')]
    #[Link(rel: 'soft404', href: 'app://self/link/jump/soft404?id={id}')]
    #[Link(rel: 'soft500', href: 'app://self/link/jump/soft500?id={id}')]
    public function onGet(int $id): static
    {
        $this->headers = ['ETag' => 'start'];
        $this->body = ['id' => $id, 'name' => 'start'];
        $this->view = 'start view';

        return $this;
    }

    #[Link(rel: 'posted', href: 'app://self/link/jump/first?id={id}')]
    public function onPost(int $id): static
    {
        return $this->onGet($id);
    }
}
