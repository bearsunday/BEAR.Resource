<?php

namespace Sandbox\Resource;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Annotation\Link;

class Blog extends ResourceObject
{
    protected $users = [
        ['name' => 'Athos blog'],
        ['name' => 'Aramis blog'],
        ['name' => 'Porthos blog']
    ];

    public function onGet($id)
    {
        return $this->users[$id];
    }
}
