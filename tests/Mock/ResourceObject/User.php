<?php

namespace testworld\ResourceObject;

use BEAR\Resource\Object as ResourceObject,
    BEAR\Resource\AbstractObject;

class User extends AbstractObject
{
    private $users = array(
            array('id' => 1, 'name' => 'Athos', 'age' => 15),
            array('id' => 2, 'name' => 'Aramis', 'age' => 16)
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

    /**
     * @Provide("id")
     */
    public function provideId()
    {
        return 1;
    }
}
