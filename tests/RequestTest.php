<?php

namespace BEAR\Resource;

use BEAR\Resource\Request\Method,
    BEAR\Resource\Adapter\Nop,
    Ray\Di\Config;

/**
 * Test class for BEAR.Resource.
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
    protected $request;

    protected function setUp()
    {
        parent::setUp();
        $this->request = new Request(new Invoker(new Config, new Linker));
    }

    public function test_New()
    {
        $this->assertInstanceOf('\BEAR\Resource\Request', $this->request);
    }

    public function test___toString()
    {
        $this->request->method = 'get';
        $this->request->ro = new Nop;
        $this->request->ro->uri = 'nop://self/path/to/resource';
        $this->request->query = array('name' => 'koriym', 'age' => 25);
        $actual = (string)$this->request;
        $this->assertSame('get nop://self/path/to/resource?name=koriym&age=25', $actual);
    }

    public function test__invoke()
    {
        $this->request->method = 'get';
        $this->request->ro = new Nop;
        $this->request->ro->uri = 'nop://self/path/to/resource';
        $this->request->query = array('a' => 'koriym', 'b' => 25);
        $request = $this->request;
        $actual = $request();
        $expected = array('koriym', 25);
        $this->assertInstanceOf('\BEAR\Resource\Request', $this->request);
        $this->assertSame($expected, $actual);
    }

    public function test__invokeWithQuery()
    {
        $this->request->method = 'get';
        $this->request->ro = new Nop;
        $this->request->ro->uri = 'nop://self/path/to/resource';
        $this->request->query = array('a' => 'koriym', 'b' => 25);
        $request = $this->request;
        $actual = $request(array('b' => 30));
        $expected = array('koriym', 30);
        $this->assertInstanceOf('\BEAR\Resource\Request', $this->request);
        $this->assertSame($expected, $actual);
    }
    
    public function test__NoUriGivenSchemeIsObject()
    {
        $this->request->method = 'get';
        $this->request->ro = new Nop;
        $this->request->query = array('a' => 'koriym', 'b' => 25);
        $actual = (string)$this->request;
        $expected = 'get object://BEAR/Resource/Adapter/Nop?a=koriym&b=25';
        $this->assertSame($expected, $actual);
    }
}