<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Resource;
use BEAR\Resource\ResourceInterface;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class ResourceModuleTest extends TestCase
{
    protected function setUp() : void
    {
        parent::setUp();
    }

    public function testConfigure()
    {
        $resource = (new Injector(new ResourceModule('FakeVendor/Sandbox')))->getInstance(ResourceInterface::class);
        $this->assertInstanceOf(Resource::class, $resource);
    }
}
