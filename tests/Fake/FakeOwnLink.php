<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;

class FakeOwnLink extends ResourceObject
{
    #[Link(rel: 'next', href: '/next')]
    public function onGet(): static
    {
        $this->headers['Link'] = '</own>; rel="own"';

        return $this;
    }

    #[Link(rel: 'next', href: '/next')]
    public function onPost(): static
    {
        return $this;
    }
}
