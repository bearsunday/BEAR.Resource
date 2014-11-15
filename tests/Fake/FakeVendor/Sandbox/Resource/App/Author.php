<?php
namespace FakeVendor\Sandbox\Resource\App;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class Author extends ResourceObject
{

    private $users = [
        0 => ['name' => 'Athos', 'age' => 15, 'blog_id' => 11],
        1 => ['name' => 'Aramis', 'age' => 16, 'blog_id' => 12],
        2 => ['name' => 'Porthos', 'age' => 17, 'blog_id' => 0]
    ];

    /**
     * @Link(rel="blog", href="app://self/blog?id={blog_id}")
     */
    public function onGet($id)
    {
        $this->body = $this->users[$id];

        return $this;
    }
}
