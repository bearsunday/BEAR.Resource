<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;

class FakePrefetchBody extends ResourceObject
{
    /** @return array{next: int} */
    #[Link(rel: 'prefetch', href: '/article/{next}')]
    public function onGet(): array
    {
        return ['next' => 2];
    }
}
