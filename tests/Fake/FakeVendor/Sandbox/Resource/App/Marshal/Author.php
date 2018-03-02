<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App\Marshal;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class Author extends ResourceObject
{
    protected $users = [
        ['id' => 0, 'name' => 'Athos'],
        ['id' => 1, 'name' => 'Aramis'],
        ['id' => 2, 'name' => 'Porthos'],
        ['name' => 'Koriym']
    ];

    /**
     * @Link(crawl="tree", rel="post", href="app://self/marshal/post?author_id={id}", method="get")
     */
    public function onGet($id = null)
    {
        return $id === null ? $this->users : $this->users[$id];
    }
}
