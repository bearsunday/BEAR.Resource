<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\Page\Layout;

use BEAR\Resource\FakeEmptyLayout;
use BEAR\Resource\ResourceObject;

#[FakeEmptyLayout]
class Bare extends ResourceObject
{
    public function onGet(): static
    {
        return $this;
    }
}
