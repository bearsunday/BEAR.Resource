<?php
/**
 * This file is part of the BEAR.Sunday package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
use BEAR\Resource\ResourceObject;

class User extends ResourceObject
{
    protected $users = [
        ['name' => 'Athos', 'age' => 15, 'blog_id' => 0],
        ['name' => 'Aramis', 'age' => 16, 'blog_id' => 1],
        ['name' => 'Porthos', 'age' => 17, 'blog_id' => 2]
    ];

    public function onGet($id)
    {
        return $this->users[$id];
    }
}
