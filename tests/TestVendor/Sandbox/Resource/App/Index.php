<?php

namespace TestVendor\Sandbox\Resource\App;

use BEAR\Resource\ResourceObject;

/**
 * Test class for root resource
 *
 * app://self/
 */
class Index extends ResourceObject
{
    public $class = __CLASS__;

    public function onGet()
    {
        return 'get';
    }
}
