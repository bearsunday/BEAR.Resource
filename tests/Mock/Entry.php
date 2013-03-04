<?php

namespace BEAR\Resource\Mock;

use BEAR\Resource\ObjectInterface as ResourceObject;
use BEAR\Resource\AbstractObject;

class Entry extends AbstractObject
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
