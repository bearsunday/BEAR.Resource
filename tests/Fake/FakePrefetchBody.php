<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;

class FakePrefetchBody extends ResourceObject
{
    /** @return array{next: int, host: string, empty: string, list: array<never>, flag: bool} */
    #[Link(rel: 'prefetch', href: '{+host}')]
    #[Link(rel: 'prefetch', href: '/tag/{empty}')]
    #[Link(rel: 'prefetch', href: '/list/{list}')]
    #[Link(rel: 'prefetch', href: '/flag/{flag}')]
    #[Link(rel: 'prefetch', href: '/article/{next}')]
    public function onGet(): array
    {
        return ['next' => 2, 'host' => '//evil.example/x', 'empty' => '', 'list' => [], 'flag' => false];
    }
}
