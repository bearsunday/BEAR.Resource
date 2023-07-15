<?php

declare(strict_types=1);

namespace BEAR\Resource\FakeVendor\Sandbox\Resource\Page;

use BEAR\Resource\FakeIntBacked;
use BEAR\Resource\FakeStringBacked;
use BEAR\Resource\ResourceObject;

final class EnumParam extends ResourceObject
{
    public function onGet(
        FakeStringBacked $stringBacked,
        FakeIntBacked $intBacked,
        FakeStringBacked|null $hasDefault = null,
    ): static {
        return $this;
    }
}
