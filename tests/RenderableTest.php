<?php

namespace BEAR\Resource;

use BEAR\Resource\Request\Method,
    BEAR\Resource\Adapter\Nop;
use Ray\Di\Config,
    Ray\Di\Annotation,
    Ray\Di\Definition;

use BEAR\Resource\Mock;

/**
 * Test class for BEAR.Resource.
 */
class RendarableTest extends \PHPUnit_Framework_TestCase
{
    protected $request;

    protected function setUp()
    {
        parent::setUp();
        $this->renderer = new Mock\JsonRenderer;
        $signal = require dirname(__DIR__) . '/vendor/Aura/Signal/scripts/instance.php';
        $this->request = new Request(new Invoker(new Config(new Annotation(new Definition)), new Linker, $signal));
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Resource\Mock\JsonRenderer', $this->renderer);
    }

    public function test__toString()
    {
        $this->request->method = 'get';
        $this->request->ro = new Nop;
        $this->request->ro->uri = 'nop://self/path/to/resource';
        $this->request->query = array('a' => 'koriym', 'b' => 25);
        $request = $this->request;
        $this->request->setRenderer($this->renderer);
        $actual = (string)$request;
        $expected = '["koriym",25]';
        $this->assertSame($expected, $actual);
    }

    public function test__toStringException()
    {
        $this->request->method = 'get';
        $this->request->ro = new Nop;
        $this->request->ro->uri = 'nop://self/path/to/resource';
        $this->request->query = array('a' => 'error', 'b' => 25, 'error' => true);
        $request = $this->request;
        $this->request->setRenderer($this->renderer);
        $actual = (string)$request;
        $expected = '';
        $this->assertSame($expected, $actual);
    }
}