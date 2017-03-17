<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Module;

use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\Resource;
use BEAR\Resource\ResourceInterface;
use Ray\Di\Injector;

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
