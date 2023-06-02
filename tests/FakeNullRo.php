<?php

declare(strict_types=1);

namespace BEAR\Resource;

final class FakeNullRo extends ResourceObject
{
    public function onGet(): static
    {
        return $this;
    }
}
