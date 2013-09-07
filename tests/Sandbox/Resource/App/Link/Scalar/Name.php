<?php
namespace Sandbox\Resource\App\Link\Scalar;

use BEAR\Resource\AbstractObject;
use BEAR\Resource\Annotation\Link;

class Name extends AbstractObject
{
    /**
     * @Link(rel="greeting", href="app://self/link/scalar/hello?name={value}", method="get")
     */
    public function onGet($name)
    {
        return $name;
    }
}
