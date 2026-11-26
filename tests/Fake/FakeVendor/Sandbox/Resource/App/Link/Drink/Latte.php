<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Link\Drink;

use BEAR\Resource\ResourceObject;

class Latte extends ResourceObject
{
    public function onGet(): static
    {
        $this->body = ['name' => 'latte'];

        return $this;
    }
}
