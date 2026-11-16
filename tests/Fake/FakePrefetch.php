<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\Annotation\Link;

class FakePrefetch extends ResourceObject
{
    #[JsonSchema(schema: 'user.json')]
    #[Link(rel: 'prefetch', href: '/user/{age}')]
    #[Link(rel: 'prefetch', href: '/friend/{friend}')]
    #[Link(rel: 'prefetch', href: '/search{?q}')]
    #[Link(rel: 'prefetch', href: '/edit', method: 'put')]
    #[Link(rel: 'prefetch', href: 'app://self/user')]
    #[Link(rel: 'next', href: '/next')]
    #[Link(rel: 'profile', href: '/profile')]
    #[Link(rel: 'prefetch', href: 'https://example.com/p2')]
    #[Link(rel: 'prefetch', href: '/p3')]
    #[Link(rel: 'prefetch', href: '/p4')]
    #[Link(rel: 'prefetch', href: '/p5')]
    #[Link(rel: 'prefetch', href: '/p6')]
    #[Link(rel: 'last', href: '/last')]
    public function onGet(int $code = 200): static
    {
        $this->code = $code;
        $this->body = ['name' => ['firstName' => 'mucha', 'lastName' => 'alfons'], 'age' => 20, 'friend' => null];

        return $this;
    }
}
