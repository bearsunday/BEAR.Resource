<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;

class FakePrefetchBody extends ResourceObject
{
    /** @return array{next: int, host: string, empty: string} */
    #[Link(rel: 'prefetch', href: '{+host}')]
    #[Link(rel: 'prefetch', href: '/tag/{empty}')]
    #[Link(rel: 'prefetch', href: '/article/{next}')]
    public function onGet(): array
    {
        return ['next' => 2, 'host' => '//evil.example/x', 'empty' => ''];
    }
}
