<?php

namespace Sandbox\Resource\Page;

use BEAR\Resource\AbstractObject;

class News extends AbstractObject
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
