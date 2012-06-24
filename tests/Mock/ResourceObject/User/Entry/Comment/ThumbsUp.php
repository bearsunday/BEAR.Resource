<?php

namespace testworld\ResourceObject\Entry\Comment;

use BEAR\Resource\Object as ResourceObject,
    BEAR\Resource\AbstractObject,
    BEAR\Resource\ResourceInterface,
    BEAR\Resource\Factory,
    BEAR\Resource\Invoker,
    BEAR\Resource\Linker,
    BEAR\Resource\Resource,
    BEAR\Resource\Request;

class ThumbsUp extends AbstractObject
{

    public function __construct()
    {
    }

    /**
     * @param id
     *
     * @return array
     */
    public function onGet($comment_id)
    {
        $thumbsup = array('up' => 30, 'down' => 10 , 'body' => "thumbsup for {$comment_id} comment");

        return $thumbsup;
    }
}
