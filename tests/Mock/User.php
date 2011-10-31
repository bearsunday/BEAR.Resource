<?php

namespace BEAR\Resource\Mock;

use BEAR\Resource\Object as ResourceObject;

class User implements ResourceObject
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
    
    public function onPost($id, $name, $age)
    {
        return "post user[{$id} {$name} {$age}]";
    }
    
    public function onPut($id = 10, $name, $age = 10)
    {
        return "put user[{$id} {$name} {$age}]";
    }
}
