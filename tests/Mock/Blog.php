<?php

namespace BEAR\Resource\Mock;

use BEAR\Resource\Object as ResourceObject;
use BEAR\Resource\AbstractObject;

class Blog extends AbstractObject
{
    /**
     * @param id
     *
     * @return array
     */
    public function onGet($id)
    {
    }
}
