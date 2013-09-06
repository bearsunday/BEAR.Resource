<?php

namespace Sandbox\Resource;

use BEAR\Resource\AbstractObject;
use BEAR\Resource\Annotation\Link;

class Entry extends AbstractObject
{
    protected $entries = [
        ['id' => 0, 'title' => '1st day', 'body' => 'It is a good day'],
        ['id' => 1, 'title' => '2nd day', 'body' => 'It is a great day'],
        ['id' => 2, 'title' => '3rd day', 'body' => 'It is a beautiful day']
    ];

    /**
     * @param $id
     *
     * @return mixed
     *
     * @Link(rel="comment", href="app://self/comment?id={id}", method="get")
     */
    public function onGet()
    {
        return $this->entries;
    }
}
