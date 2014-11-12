<?php

namespace BEAR\Resource\Adapter;

use BEAR\Resource\Uri;
use Ray\Di\Injector;

class AppTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $app = new App(new Injector,'BEAR\Resource');
        $resourceObject = $app->get(new Uri('page://self/resource/foo'));
        $this->assertInstanceOf('BEAR\Resource\Resource\Foo', $resourceObject);
    }
}
