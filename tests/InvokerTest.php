<?php

namespace BEAR\Resource;

use BEAR\Resource\Mock\User;

use Ray\Di\Annotation,
    Ray\Di\Config,
    Ray\Di\Forge,
    Ray\Di\Container,
    Ray\Di\Manager,
    Ray\Di\Injector,
    Ray\Di\EmptyModule,
    BEAR\Resource\Invoker;

/**
 * Test class for PHP.Skelton.
 */
class InvokerTest extends \PHPUnit_Framework_TestCase
{
    protected $invoker;

    protected function setUp()
    {
        parent::setUp();
        $schemeAdapters = array('nop' => '\BEAR\Resource\Adapter\Nop', 'prov' => '\BEAR\Resource\Mock\Prov');
        $injector = new Injector(new Container(new Forge(new Config(new Annotation()))), new EmptyModule());
        $factory = new Factory($injector, $schemeAdapters);
        //         $factory->newInstance('nop://');
        $resource = new User();
        $resource->uri = 'dummy://self/User';
        $this->request = new Request();
        $this->request->method = 'get';
        $this->request->ro = $resource;
        $this->request->query = array('id' => 1);
        $this->invoker = new Invoker($injector);
    }

    public function test_Invoke()
    {
        $actual = $this->invoker->invoke($this->request);
        $expected = array('id' => 2, 'name' => 'Aramis', 'age' => 16);
        $this->assertSame($actual, $expected);
    }

    public function test_InvokeMissingParam()
    {
        $this->request->query = array();
        $actual = $this->invoker->invoke($this->request);
        $expected = array('id' => 2, 'name' => 'Aramis', 'age' => 16);
        $this->assertSame($actual, $expected);
    }

    public function test_InvokeDefaultParam()
    {
        $this->request->query = array();
        $this->request->method = 'post';
        $this->query = array('id' => 1);
        $actual = $this->invoker->invoke($this->request);
        $expected = 'post user[1 default_name 99]';
        $this->assertSame($actual, $expected);
    }

    /**
     * @expectedException BEAR\Resource\Exception\InvalidParameter
     */
    public function test_InvokeDefaultParamWithNoProvider()
    {
        $this->request->query = array();
        $this->request->method = 'put';
        $this->query = array();
        $actual = $this->invoker->invoke($this->request);
    }

    /**
     * @expectedException BEAR\Resource\Exception\InvalidParameter
     */
    public function test_InvokeWithNoProvider()
    {
        $this->request->ro = new Mock\Blog;
        $this->request->query = array();
        $this->request->method = 'get';
        $actual = $this->invoker->invoke($this->request);
    }

    public function test_InvokeWithUnspecificProvider()
    {
        $this->request->ro = new Mock\Entry;
        $this->request->query = array();
        $this->request->method = 'get';
        $actual = $this->invoker->invoke($this->request);
        $this->assertSame('entry1', $actual);
    }

    /**
     * @expectedException BEAR\Resource\Exception\InvalidParameter
     */
    public function test_InvokeWithUnspecificProviderButNoResult()
    {
        $this->request->ro = new Mock\Comment;
        $this->request->query = array();
        $this->request->method = 'get';
        $actual = $this->invoker->invoke($this->request);
        $this->assertSame('entry1', $actual);
    }

    /**
     * @expectedException BEAR\Resource\Exception\InvalidMethod
     */
    public function test_InvokeInvalidMethod()
    {
        $this->request->method = 'InvalidMethod';
        $actual = $this->invoker->invoke($this->request);
        $expected = array('id' => 2, 'name' => 'Aramis', 'age' => 16);
        $this->assertSame($actual, $expected);
    }

}