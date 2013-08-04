<?php

namespace BEAR\Resource\Mock;

use BEAR\Resource\AbstractObject;
use BEAR\Resource;

class User extends AbstractObject
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
