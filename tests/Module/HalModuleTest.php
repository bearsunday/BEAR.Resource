<?php

namespace BEAR\Resource\Module;

use Ray\Di\Injector;
use BEAR\Resource\UriMapperInterface;
use BEAR\Resource\UriMapper;

class HalModuleTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $injector = new Injector(new HalModule);
        $uriMapper = $injector->getInstance(UriMapperInterface::class);
        $this->assertInstanceOf(UriMapper::class, $uriMapper);
    }
}
