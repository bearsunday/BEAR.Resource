<?php
namespace Sandbox\Resource\App;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Annotation\Link;

class Author extends ResourceObject
{
    protected $users = [
        ['id' => 1, 'name' => 'Athos'],
        ['id' => 2, 'name' => 'Aramis'],
        ['id' => 3, 'name' => 'Porthos']
    ];

    /**
     * @Link(crawl="tree", rel="post", href="app://self/post?author_id={id}")
     */
    public function onGet($id = null)
    {
        return is_null($id) ? $this->users : $this->users[$id];
    }
}
