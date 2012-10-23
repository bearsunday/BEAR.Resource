<?php

namespace BEAR\Resource;

use BEAR\Resource\Request\Method;
use BEAR\Resource\Adapter\Nop;
use BEAR\Resource\Adapter\Test;
use Ray\Di\Config;
use Ray\Di\Annotation;
use Ray\Di\Definition;
use Doctrine\Common\Annotations\AnnotationReader as Reader;

/**
 * Test class for BEAR.Resource.
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
    protected $request;

    protected function setUp()
    {
        parent::setUp();
        $signal = require dirname(__DIR__) . '/vendor/aura/signal/scripts/instance.php';
        $this->request = new Request(new Invoker(new Config(new Annotation(new Definition, new Reader)), new Linker(new Reader), $signal));
    }

    public function test_New()
    {
        $this->assertInstanceOf('\BEAR\Resource\Request', $this->request);
    }

    public function test_toUriWithMethod()
    {
        $this->request->method = 'get';
        $this->request->ro = new Test;
        $this->request->ro->uri = 'test://self/path/to/resource';
        $this->request->query = array('a' => 'koriym', 'b' => 25);
        $actual = $this->request->toUriWithMethod();
        $this->assertSame('get test://self/path/to/resource?a=koriym&b=25', $actual);
    }

    public function test_toUri()
    {
        $this->request->method = 'get';
        $this->request->ro = new Test;
        $this->request->ro->uri = 'test://self/path/to/resource';
        $this->request->query = array('a' => 'koriym', 'b' => 25);
        $actual = $this->request->toUri();
        $this->assertSame('test://self/path/to/resource?a=koriym&b=25', $actual);
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

    public function test__toStringWithRenderableResourceObject()
    {
        $this->request->method = 'get';
        $this->request->ro = new Test;
        $renderer = new TestRenderer;
        $this->request->ro->setRenderer($renderer);
        $this->request->ro->uri = 'nop://self/path/to/resource';
        $this->request->query = array('a' => 'koriym', 'b' => 25);
        $request = $this->request;
        $actual = $request(array('b' => 30));
        $expected = array('koriym', 30);
        $this->assertInstanceOf('\BEAR\Resource\Request', $this->request);
        $request = $this->request;
        $result = (string) $request;
        $this->assertSame('{"posts":["koriym",30]}', $result);
    }
    public function test__toStringWithErrorRenderer()
    {
        $this->request->method = 'get';
        $this->request->ro = new Test;
        $renderer = new ErrorRenderer;
        $this->request->ro->setRenderer($renderer);
        $this->request->ro->uri = 'nop://self/path/to/resource';
        $this->request->query = array('a' => 'koriym', 'b' => 25);
        $request = $this->request;
        $result = (string) $request;
        $this->assertSame($result, '');
    }

    public function test__toStringWithoutRender()
    {
        $this->request->method = 'get';
        $this->request->ro = new Test;
        $this->request->ro->uri = 'nop://self/path/to/resource';
        $this->request->query = array('a' => 'koriym', 'b' => 25);
        $request = $this->request;
        $this->assertInstanceOf('\BEAR\Resource\Request', $this->request);
        $request = $this->request;
        $result = (string) $request;
        $this->assertSame('', $result);
    }
}
