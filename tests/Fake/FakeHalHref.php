<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;

class FakeHalHref extends ResourceObject
{
    #[Link(rel: 'next', href: '/posts{?page}', title: 'Next page')]
    #[Link(rel: 'first', href: '/posts')]
    public function onGet(): static
    {
        return $this;
    }
}
