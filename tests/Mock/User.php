<?php

namespace BEAR\Resource\Mock;

use BEAR\Resource\ObjectInterface as ResourceObject;
use BEAR\Resource\AbstractObject;
use BEAR\Resource\Link;

class User extends AbstractObject
{
    public $links = [
        'friend' => [Link::HREF => 'app://self/friend/{?id}', Link::TEMPLATED => true],
        'profile' => [Link::HREF => 'app://self/profile']
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
