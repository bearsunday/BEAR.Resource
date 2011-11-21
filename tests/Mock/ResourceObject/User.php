<?php

namespace testworld\ResourceObject;

use BEAR\Resource\Object as ResourceObject,
    BEAR\Resource\AbstractObject,
    BEAR\Resource\Resource;

class User extends AbstractObject
{
    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
    }

    private $users = array(
            array('id' => 1, 'name' => 'Athos', 'age' => 15, 'blog_id' => 11),
            array('id' => 2, 'name' => 'Aramis', 'age' => 16, 'blog_id' => 12),
            array('id' => 3, 'name' => 'Porthos', 'age' => 17, 'blog_id' => 0)
    );

    /**
     * @param id
     *
     * @return array
     */
    public function onGet($id)
    {
        if (!isset($this->users[$id])) {
            throw new \InvalidArgumentException($id);
        }
        return $this->users[$id];
    }


    public function onPost($id, $name='default_name', $age=99)
    {
        return "post user[{$id} {$name} {$age}]";
    }

    public function onPut($noprovide)
    {
        return "put user[{$id} {$name} {$age}]";
    }

    public function onDelete()
    {
        return "deleted";
    }

    public function onLinkBlog(array $user)
    {
        return $this->resource
        ->get->uri('app://self/Blog')->withQuery(['id' => $user['blog_id']])->request();
    }
    /**
     * @Provides("id")
     */
    public function provideId()
    {
        return 1;
    }
}
