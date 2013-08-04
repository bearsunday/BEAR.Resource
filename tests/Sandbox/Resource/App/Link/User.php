<?php
namespace Sandbox\Resource\App\Link;

use BEAR\Resource\ObjectInterface as ResourceObject;
use BEAR\Resource\AbstractObject;
use BEAR\Resource\Annotation\Link;

/** @noinspection PhpUndefinedClassInspection */
class User extends AbstractObject implements ResourceObject
{

    private $users = [
        0 => ['name' => 'Athos', 'age' => 15, 'blog_id' => 11],
        1 => ['name' => 'Aramis', 'age' => 16, 'blog_id' => 12],
        2 => ['name' => 'Porthos', 'age' => 17, 'blog_id' => 0]
    ];

    /**
     * @Link(rel="blog", href="app://self/Link/Blog", method="get")
     */
    public function onGet($id)
    {
        return $this->users[$id];
    }
}
