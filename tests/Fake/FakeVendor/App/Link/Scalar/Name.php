<?php
namespace FakeVendor\Sandbox\Resource\App\Link\Scalar;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Annotation\Link;

class Name extends ResourceObject
{
    /**
     * @Link(rel="greeting", href="app://self/link/scalar/hello?name={value}", method="get")
     * @Link(rel="no_query", href="app://self/link/scalar/hello", method="get")
     */
    public function onGet($name)
    {
        return $name;
    }
}
