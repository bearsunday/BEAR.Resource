<?php

namespace BEAR\Resource\Mock;

use BEAR\Resource\Object as ResourceObject,
    BEAR\Resource\AbstractObject;

class Comment extends AbstractObject
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
     * @Provides
     */
    public function provideId()
    {
        return array('aaa'=>1);
    }
}
