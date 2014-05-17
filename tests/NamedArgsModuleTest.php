<?php

namespace BEAR\Resource;

use BEAR\Resource\Module\NamedArgsModule;
use Ray\Di\Injector;
use Ray\Aop\NamedArgsInterface;
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
        $app = Injector::create([new NamedArgsModule])->getInstance(__NAMESPACE__ . '\Dependent');
        $this->assertInstanceOf('Ray\Aop\NamedArgsInterface' , $app->namedArgs);
    }
}
