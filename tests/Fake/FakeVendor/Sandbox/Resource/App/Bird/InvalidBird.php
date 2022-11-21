<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Bird;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceObject;
use Ray\Di\Di\Named;

class InvalidBird extends ResourceObject
{
    #[Embed(rel: "bird1", src: "invalid_uri")]
    public function onGet(int $id)
    {
        unset($id);

        return $this;
    }
}
