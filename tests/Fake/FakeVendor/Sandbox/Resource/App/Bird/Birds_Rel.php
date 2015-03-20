<?php

namespace FakeVendor\Sandbox\Resource\App\Bird;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class Birds_Rel extends ResourceObject
{
    /**
     * @Embed(rel="bird1", src="/bird/canary")
     * @Embed(rel="bird2", src="/bird/sparrow{?id}")
     *
     * @Link(rel="bird3", href="/bird/suzume")
     */
    public function onGet($id)
    {
        return $this;
    }
}
