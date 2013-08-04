<?php

namespace Sandbox\Resource\App;

use BEAR\Resource\AbstractObject;

class Index extends AbstractObject
{
    public $class = __CLASS__;

    public function onGet()
    {
        return 'get';
    }
}
