<?php

namespace BEAR\Resource;

use BEAR\Resource\Module\NamedArgsModule;
use Ray\Di\Injector;
use Ray\Di\Di\Inject;

class Dependent
{
    public $namedArgs;

    /**
     * @Inject
     */
    public function setCache(NamedArgsInterface $namedArgs)
    {
        $this->namedArgs = $namedArgs;
    }
}

class NamedArgsTest extends \PHPUnit_Framework_TestCase
{
    public function testCacheApc()
    {
        $app = (new Injector(new NamedArgsModule))->getInstance(__NAMESPACE__ . '\Dependent');
        $this->assertInstanceOf('BEAR\Resource\NamedArgsInterface' , $app->namedArgs);
    }
}
