<?php
namespace Sandbox\Resource\App\Marshal;

use BEAR\Resource\ObjectInterface as ResourceObject;
use BEAR\Resource\AbstractObject;
use BEAR\Resource\Annotation\Link;

class Author extends AbstractObject implements ResourceObject
{

    protected $users = [
        ['id' => 1, 'name' => 'Athos'],
        ['id' => 2, 'name' => 'Aramis'],
        ['id' => 3, 'name' => 'Porthos']
    ];

    /**
     * @Link(crawl="tree", rel="post", href="app://self/marshal/post?author_id={id}", method="get")
     */
    public function onGet($id = null)
    {
        return is_null($id) ? $this->users : $this->users[$id];
    }
}
