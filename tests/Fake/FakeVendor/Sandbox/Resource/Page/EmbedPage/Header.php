<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\Page\EmbedPage;

use BEAR\Resource\ResourceObject;

class Header extends ResourceObject
{
    public function onGet(): static
    {
        $this->body = ['title' => 'header'];

        return $this;
    }
}
