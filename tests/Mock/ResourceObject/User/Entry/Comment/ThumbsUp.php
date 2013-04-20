<?php

namespace testworld\ResourceObject\Entry\Comment;

use BEAR\Resource\AbstractObject;
use BEAR\Resource\Invoker;

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
        $thumbsup = array('up' => 30, 'down' => 10, 'body' => "thumbsup for {$comment_id} comment");

        return $thumbsup;
    }
}
