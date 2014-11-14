<?php
namespace FakeVendor\Sandbox\Resource\App\Link\Scalar;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Annotation\Link;

class Name extends ResourceObject
{
    /**
     * @Link(rel="greeting", href="app://self/link/scalar/hello?name={value}")
     * @Link(rel="no_query", href="app://self/link/scalar/hello")
     */
    public function onGet($name)
    {
        return $name;
    }
}
