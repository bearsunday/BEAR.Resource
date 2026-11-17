<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\Page\Layout;

use BEAR\Resource\FakeUndeclaredLayout;
use BEAR\Resource\ResourceObject;

#[FakeUndeclaredLayout]
class Undeclared extends ResourceObject
{
    public function onGet(): static
    {
        return $this;
    }
}
