<?php

namespace BEAR\Resource\Adapter;

use BEAR\Resource\Uri;
use Ray\Di\Injector;
use FakeVendor\Sandbox\Resource\Page\Index;

class AppTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $app = new App(new Injector,'FakeVendor\Sandbox');
        $resourceObject = $app->get(new Uri('page://self/index'));
        $this->assertInstanceOf(Index::class, $resourceObject);
    }
}
