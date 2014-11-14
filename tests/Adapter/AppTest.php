<?php

namespace BEAR\Resource;

use Ray\Di\Injector;
use FakeVendor\Sandbox\Resource\Page\Index;

class AppTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $app = new AppAdapter(new Injector,'FakeVendor\Sandbox');
        $resourceObject = $app->get(new Uri('page://self/index'));
        $this->assertInstanceOf(Index::class, $resourceObject);
    }
}
