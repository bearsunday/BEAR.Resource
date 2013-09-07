<?php
namespace Sandbox\Resource\App\Link\Scalar;

use BEAR\Resource\AbstractObject;
use BEAR\Resource\Annotation\Link;

class Hello extends AbstractObject
{
    public function onGet($name)
    {
        return "hell {$name}";
    }
}
