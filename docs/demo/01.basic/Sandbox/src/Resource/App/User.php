<?php

namespace Sandbox\Resource\Resource\App;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Annotation\Link;

class User extends ResourceObject
{

    protected $users = [
        ['name' => 'Athos', 'age' => 15, 'blog_id' => 0],
        ['name' => 'Aramis', 'age' => 16, 'blog_id' => 1],
        ['name' => 'Porthos', 'age' => 17, 'blog_id' => 2]
    ];

    /**
     * @Link(rel="blog", href="app://self/link/blog?blog_id={blog_id}", method="get")
     */
    public function onGet($id)
    {
        return $this->users[$id];
    }
}
