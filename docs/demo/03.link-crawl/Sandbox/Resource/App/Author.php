<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Sandbox\Resource\App;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

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
        return $id === null ? $this->users : $this->users[$id];
    }
}
