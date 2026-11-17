<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Link;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class HrefFragment extends ResourceObject
{
    #[Link(rel: 'expanded', href: 'app://self/author{?id}{+fragment}')]
    #[Link(crawl: 'expanded', rel: 'author', href: 'app://self/author{?id}{+fragment}')]
    public function onGet(): static
    {
        $this->body = ['id' => 1, 'fragment' => '#link=self:blog'];

        return $this;
    }
}
