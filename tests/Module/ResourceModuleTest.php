<?php

namespace Module;

use BEAR\Resource\Module\ResourceModule;
use Ray\Di\Injector;
use BEAR\Resource\Resource;
use BEAR\Resource\ResourceInterface;

class ResourceModuleTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testConfigure()
    {
        $resource = (new Injector(new ResourceModule('FakeVendor/Sandbox')))->getInstance(ResourceInterface::class);
        $this->assertInstanceOf(Resource::class, $resource);
    }
}
