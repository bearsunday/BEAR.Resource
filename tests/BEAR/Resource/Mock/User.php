<?php

namespace BEAR\Resource\Mock;

use BEAR\Resource\ResourceObject;
use BEAR\Resource;
use BEAR\Resource\Annotation\Link;

class User extends ResourceObject
{
    public $links = [
        'friend' => [Resource\Link::HREF => 'app://self/friend/{?id}', Resource\Link::TEMPLATED => true],
        'profile' => [Resource\Link::HREF => 'app://self/profile']
    ];

    public $body = [
        'id' => 1,
        'name' => 'koriym'
    ];

    /**
     * @Link(rel="friend",  href="app://self/friend/{?id}")
     * @Link(rel="profile", href="app://self/profile{?id}")
     */
    public function onGet($id)
    {
        return $this;
    }
}
