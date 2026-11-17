<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Link;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class ReplacedRow extends ResourceObject
{
    #[Link(crawl: 'replaced', rel: 'ids', href: 'app://self/link/ids')]
    #[Link(crawl: 'replaced', rel: 'author', href: 'app://self/author?id={ids}')]
    public function onGet(): static
    {
        $this->body = [['ids' => [['id' => 99]]]];

        return $this;
    }
}
