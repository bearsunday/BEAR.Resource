<?php

namespace BEAR\Resource\Mock;

use BEAR\Resource\Object as ResourceObject,
    BEAR\Resource\AbstractObject;

class Entry extends AbstractObject
{
    /**
     * @param id
     *
     * @return array
     */
    public function onGet($id)
    {
        return "entry{$id}";
    }

    /**
     * @Provide
     */
    public function provideId()
    {
        return array('id'=>1);
    }
}
