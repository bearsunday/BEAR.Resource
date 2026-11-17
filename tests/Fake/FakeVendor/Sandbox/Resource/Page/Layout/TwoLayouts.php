<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\Page\Layout;

use BEAR\Resource\FakeContentPage;
use BEAR\Resource\FakeSearchPage;
use BEAR\Resource\ResourceObject;

#[FakeContentPage]
#[FakeSearchPage]
class TwoLayouts extends ResourceObject
{
    public function onGet(): static
    {
        return $this;
    }
}
