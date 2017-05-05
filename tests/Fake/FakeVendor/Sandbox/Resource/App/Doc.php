<?php

namespace FakeVendor\Sandbox\Resource\App;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class Doc extends ResourceObject
{
    public $uri = 'app://self/Doc';
    /**
     * User
     *
     * Returns a variety of information about the user specified by the required $id parameter
     *
     * @param string $id User ID
     *
     * @Link(rel="friend", href="/fiend{?id}")
     * @Link(rel="task", href="/task{?id}")
     * @Embed(rel="profile", src="/profile{?id}")
     */
    public function onGet($id)
    {
        return $this;
    }

    /**
     * @param int    $id   id
     * @param string $name name
     * @param int    $age  age
     */
    public function onPost($id, $name = 'default_name', $age = 99)
    {
        return $this;
    }
}
