<?php
namespace sandbox\App\Link;

use BEAR\Resource\ObjectInterface as ResourceObject;
use BEAR\Resource\AbstractObject;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\Annotation\Link;

class User extends AbstractObject implements ResourceObject
{

    private $users = [
        ['id' => 1, 'name' => 'Athos', 'age' => 15, 'blog_id' => 11],
        ['id' => 2, 'name' => 'Aramis', 'age' => 16, 'blog_id' => 12],
        ['id' => 3, 'name' => 'Porthos', 'age' => 17, 'blog_id' => 0]
    ];

    /**
     * @Link(rel="blog", href="app://self/Blog")
     * @Inject
     */
    public function gggggggg($id)
    {
    }

    /**
     * @Link(rel="blog", href="app://self/Blog")
     */
    public function onGet($id)
    {
        return $this->users[$id];
    }
}
