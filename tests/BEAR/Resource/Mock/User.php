<?php

namespace BEAR\Resource\Mock;

use BEAR\Resource\ResourceObject;
use BEAR\Resource;

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
     * @param id
     *
     * @return array
     */
    public function onGet($id)
    {
        return $this;
    }
}
