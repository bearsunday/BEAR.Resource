<?php

namespace BEAR\Resource\Mock;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Annotation\Provides;

class Comment extends ResourceObject
{
    /**
     * @param id
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
        return array('aaa' => 1);
    }
}
