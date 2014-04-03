<?php

namespace TestVendor\Sandbox\Resource\App\Factory;

use BEAR\Resource\ResourceObject as Ro;

class News extends Ro
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
