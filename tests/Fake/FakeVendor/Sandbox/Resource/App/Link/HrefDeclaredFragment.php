<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Link;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class HrefDeclaredFragment extends ResourceObject
{
    #[Link('next', '/author?id=1#top')]
    public function onGet(): static
    {
        $this->body = ['id' => 1];

        return $this;
    }
}
