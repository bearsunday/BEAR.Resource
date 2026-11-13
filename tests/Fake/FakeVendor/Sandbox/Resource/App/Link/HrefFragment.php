<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Link;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class HrefFragment extends ResourceObject
{
    #[Link(rel: 'linked', href: 'app://self/author?id=1#link=self:blog')]
    #[Link(rel: 'anchored', href: 'app://self/author?id=1#profile')]
    #[Link(crawl: 'linked', rel: 'author', href: 'app://self/author?id=1#link=self:blog')]
    public function onGet(): static
    {
        $this->body = [['id' => 1]];

        return $this;
    }
}
