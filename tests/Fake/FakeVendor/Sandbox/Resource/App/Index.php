<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App;

use BEAR\Resource\ResourceObject;

class Index extends ResourceObject
{
    public function onGet()
    {
        return 'get';
    }
}
