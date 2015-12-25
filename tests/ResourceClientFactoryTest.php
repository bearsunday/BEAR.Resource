<?php

namespace BEAR\Resource;

use Ray\Di\EmptyModule;
use Ray\Di\Injector;

class ResourceClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testResourceClientFactory()
    {
        $factory = new ResourceClientFactory;
        $resource = $factory->newInstance(
            new Injector(new EmptyModule),
            'MyVendor/Foo'
        );
        $this->assertInstanceOf(ResourceInterface::class, $resource);
    }
}
