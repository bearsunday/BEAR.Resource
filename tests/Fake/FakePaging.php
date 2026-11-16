<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;

class FakePaging extends ResourceObject
{
    #[Link(rel: 'canonical', href: 'https://example.com/articles')]
    #[Link(rel: 'first', href: '/articles?page=1')]
    #[Link(rel: 'prev', href: '/articles?page={prev}')]
    #[Link(rel: 'next', href: '/articles?page={next}')]
    #[Link(rel: 'last', href: '/articles?page={last}')]
    public function onGet(): static
    {
        $this->body = ['prev' => null, 'next' => 2, 'last' => 9];

        return $this;
    }
}
