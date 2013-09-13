<?php

namespace Sandbox\Resource\App;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Annotation\Link;

class User extends ResourceObject
{

    protected $users = [
        ['name' => 'Athos', 'age' => 15, 'blog_id' => 0],
        ['name' => 'Aramis', 'age' => 16, 'blog_id' => 1],
        ['name' => 'Porthos', 'age' => 17, 'blog_id' => 2]
    ];

    public function onGet($user_id)
    {
        return $this->users[$user_id];
    }

    public function onDelete($delete_id)
    {
        unset($this->users[$delete_id]);
        $this->code = 203;

        return $this;
    }

    public function onProvidesDeleteId()
    {
        return 1;
    }
}
