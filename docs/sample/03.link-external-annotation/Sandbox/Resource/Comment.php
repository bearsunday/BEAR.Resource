<?php

namespace Sandbox\Resource;

use BEAR\Resource\AbstractObject;
use BEAR\Resource\Annotation\Link;

class Comment extends AbstractObject
{
    protected $comments = [
        ['id' => 0, 'body' => 'good post !'],
        ['id' => 1, 'body' => 'great post !'],
        ['id' => 2, 'body' => 'beautiful post !']
    ];

    /**
     * @param $id
     *
     * @return array
     *
     * @Link(rel="nice", href="app://self/nice?id={id}", method="get")
     */
    public function onGet($id)
    {
        $comment =  $this->comments[$id];
        return $comment;
    }
}
