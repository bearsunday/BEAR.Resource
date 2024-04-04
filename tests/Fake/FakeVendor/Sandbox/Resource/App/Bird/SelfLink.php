<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Bird;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class SelfLink extends ResourceObject
{
    #[Embed(rel: "_self", src: "app://self/bird/child{?id}")]
    public function onGet(string $id)
    {
        return $this;
    }
}
