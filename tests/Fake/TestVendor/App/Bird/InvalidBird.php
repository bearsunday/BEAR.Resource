<?php

namespace TestVendor\Sandbox\Resource\App\Bird;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Annotation\Embed;
use Ray\Di\Di\Named;

class InvalidBird extends ResourceObject
{
    /**
     * @Named
     * @Embed(rel="bird1", src="invalid_uri")
     */
    public function onGet($id)
    {
        return $this;
    }
}
