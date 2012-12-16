<?php

namespace BEAR\Resource;

use BEAR\Resource\Request\Method;
use testworld\ResourceObject\User\Entry;
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
        $actual = $request()->body;
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
        $actual = $request(array('b' => 30))->body;
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
        $actual = $request(array('b' => 30))->body;
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
        $result = (string) $request;
        $this->assertSame('', $result);
    }

    /**
     * @test
     */
    public function request()
    {
        $this->request->method = 'get';
        $this->request->ro = new Entry;
        $this->request->ro->uri = 'nop://self/path/to/resource';
        $this->request->query = [];
        $this->assertInstanceOf('\BEAR\Resource\Request', $this->request);
        return $this->request;
    }

    /**
     * @depends request
     */
    public function testIterator(Request $request)
    {
        $result = [];
        foreach($request as $row) {
            $result[] = $row;
        }
        $expected = array (
            0 =>
            array (
                'id' => 100,
                'title' => 'Entry1',
            ),
            1 =>
            array (
                'id' => 101,
                'title' => 'Entry2',
            ),
            2 =>
            array (
                'id' => 102,
                'title' => 'Entry3',
            ),
        );
        $this->assertSame($expected, $result);
    }

    /**
     * @depends request
     */
    public function test_ArrayAccess(Request $request)
    {
        $result = $request[100];
        $expected = array (
            'id' => 100,
            'title' => 'Entry1',
        );
        $this->assertSame($expected, $result);
    }

    /**
     * @depends request
     * @expectedException OutOfBoundsException
     */
    public function test_ArrayAccessNotExists(Request $request)
    {
        $this->request->method = 'get';
        $this->request->ro = new Entry;
        $request = $this->request;
        $result = $request[0];
    }

    /**
     * @depends request
     */
    public function test_IsSet(Request $request)
    {
        $result = isset($request[100]);
        $this->assertTrue($result);
    }

    /**
     * @depends request
     */
    public function test_IsSetNot(Request $request)
    {
        $this->request->method = 'get';
        $this->request->ro = new Entry;
        $this->request->query = [];
        $request = $this->request;
        $result = isset($request[0]);
        $this->assertFalse($result);
    }
}
