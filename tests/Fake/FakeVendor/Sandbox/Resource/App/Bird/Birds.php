<?php

namespace FakeVendor\Sandbox\Resource\App\Bird;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class Birds extends ResourceObject
{
    /**
     * @Embed(rel="bird1", src="app://self/bird/canary")
     * @Embed(rel="bird2", src="app://self/bird/sparrow{?id}")
     *
     * @Link(rel="bird3", href="app://self/bird/suzume")
     */
    public function onGet($id)
    {
        return $this;
    }
}
