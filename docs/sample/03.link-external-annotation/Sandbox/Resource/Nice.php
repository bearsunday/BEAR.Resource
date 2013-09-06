<?php

namespace Sandbox\Resource;

use BEAR\Resource\AbstractObject;
use BEAR\Resource\Annotation\Link;

class Nice extends AbstractObject
{
    protected $nice = [
        ['id' => 0, 'nice' => 10],
        ['id' => 1, 'nice' => 20],
        ['id' => 2, 'nice' => 30],
    ];

    /**
     * @param $id
     *
     * @return array
     */
    public function onGet($id)
    {
        $nice =  $this->nice[$id];
        return $nice;
    }
}
