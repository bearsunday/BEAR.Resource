<?php

namespace FakeVendor\Sandbox\Resource\App\Bird;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Annotation\Embed;

class NotFoundBird extends ResourceObject
{
    /**
     * @Embed(rel="bird1", src="app://self/not_found_uri")
     */
    public function onGet($id)
    {
        return $this;
    }
}
