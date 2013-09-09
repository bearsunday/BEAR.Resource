<?php

namespace BEAR\Resource\Mock;

use BEAR\Resource\ResourceObject;

class Entry extends ResourceObject
{
    /**
     * @param id
     *
     * @return array
     */
    public function onGet($id)
    {
        return "entry {$id}";
    }

    /**
     * @Provides
     */
    public function provideId()
    {
        return array('id' => 1);
    }
}
