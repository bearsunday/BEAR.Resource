<?php

namespace Sandbox\Resource\App;

use BEAR\Resource\AbstractObject;

/**
 * Test class for root resource
 *
 * app://self/
 */
class Index extends AbstractObject
{
    public $class = __CLASS__;

    public function onGet()
    {
        return 'get';
    }
}
