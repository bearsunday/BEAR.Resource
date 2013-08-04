<?php

namespace Sandbox\Resource\App;

use BEAR\Resource\AbstractObject as Ro;

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
