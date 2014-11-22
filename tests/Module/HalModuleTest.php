<?php

namespace BEAR\Resource\Module;

use BEAR\Resource\UriMapper;
use BEAR\Resource\UriMapperInterface;
use Ray\Di\Injector;

class HalModuleTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $injector = new Injector(new HalModule);
        $uriMapper = $injector->getInstance(UriMapperInterface::class);
        $this->assertInstanceOf(UriMapper::class, $uriMapper);
    }
}
