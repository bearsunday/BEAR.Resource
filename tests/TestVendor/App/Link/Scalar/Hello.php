<?php
namespace TestVendor\Sandbox\Resource\App\Link\Scalar;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Annotation\Link;

class Hello extends ResourceObject
{
    public function onGet($name)
    {
        return "hell {$name}";
    }
}
