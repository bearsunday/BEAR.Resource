<?php

namespace FakeVendor\Sandbox\Resource\Page;

use BEAR\Resource\ResourceObject;

class News extends ResourceObject
{
    /**
     * @param id
     *
     * @return array
     */
    public function onGet($id)
    {
        return __CLASS__ . $id;
    }
}
