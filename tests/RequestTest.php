<?php

namespace BEAR\Resource;

use Aura\Signal\Manager;
use Aura\Signal\HandlerFactory;
use Aura\Signal\ResultFactory;
use Aura\Signal\ResultCollection;
use BEAR\Resource\Adapter\NopResource;
use TestVendor\Sandbox\Resource\App\User\Entry;
use BEAR\Resource\Adapter\Nop;
use BEAR\Resource\Adapter\TestResource;
use Ray\Di\Definition;
use Doctrine\Common\Annotations\AnnotationReader as Reader;
use BEAR\Resource\Renderer\TestRenderer;
use BEAR\Resource\Renderer\ErrorRenderer;

/**
 * Test class for BEAR.Resource.
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Request
     */
    protected $request;

    protected function setUp()
    {
        $signal = new Manager(new HandlerFactory, new ResultFactory, new ResultCollection);
        $params = new NamedParameter(new SignalParameter($signal, new Param));
        $invoker = new Invoker(new Linker(new Reader), $params);
        $this->request = new Request($invoker);
    }

    public function testNew()
    {
        $this->assertInstanceOf('\BEAR\Resource\Request', $this->request);
    }

    public function testToUriWithMethod()
    {
        $this->request->set(new TestResource, 'test://self/path/to/resource', 'get', ['a' => 'koriym', 'b' => 25]);
        $actual = $this->request->toUriWithMethod();
        $this->assertSame('get test://self/path/to/resource?a=koriym&b=25', $actual);
    }

    public function testToUri()
    {
        $this->request->set(new TestResource, 'test://self/path/to/resource', 'get', ['a' => 'koriym', 'b' => 25]);
        $actual = $this->request->toUri();
        $this->assertSame('test://self/path/to/resource?a=koriym&b=25', $actual);
    }

    public function testInvoke()
    {
        $this->request->set(new NopResource, 'nop://self/path/to/resource', 'get', ['a' => 'koriym', 'b' => 25]);
        $request = $this->request;
        $actual = $request()->body;
        $expected = array('koriym', 25);
        $this->assertInstanceOf('\BEAR\Resource\Request', $this->request);
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\LogicException
     */
    public function testOffsetSet()
    {
        $this->request->set(new NopResource, 'nop://self/path/to/resource', 'put', ['key' => 'animal', 'value' => 'kuma']);
        $request = $this->request;
        $request['animal'] = 'cause_exception';
    }

    /**
     * @expectedException \BEAR\Resource\Exception\LogicException
     */
    public function testOffsetUnset()
    {
        $this->request->set(new NopResource, 'nop://self/path/to/resource', 'put', ['key' => 'animal', 'value' => 'kuma']);
        $request = $this->request;
        unset($request['animal']);
    }

    public function testInvokeWithQuery()
    {
        $this->request->set(new NopResource, 'nop://self/path/to/resource', 'get', ['a' => 'koriym', 'b' => 25]);
        $request = $this->request;
        $actual = $request(['b' => 30])->body;
        $expected = array('koriym', 30);
        $this->assertInstanceOf('\BEAR\Resource\Request', $this->request);
        $this->assertSame($expected, $actual);
    }

    public function testToStringWithRenderableResourceObject()
    {
        $ro = (new TestResource)->setRenderer(new TestRenderer);
        /**  @var $ro ResourceObject */
        $this->request->set($ro, 'nop://self/path/to/resource', 'get', ['a' => 'koriym', 'b' => 25]);
        $request = $this->request;
        $actual = $request(['b' => 30])->body['posts'];
        $expected = ['koriym', 30];
        $this->assertSame($expected, $actual);
        $this->assertInstanceOf('\BEAR\Resource\Request', $this->request);
        $request = $this->request;
        $result = (string)$request;
        $this->assertSame('{"posts":["koriym",30]}', $result);
    }

    public function testToStringWithErrorRenderer()
    {
        $this->request->method = 'get';
        $this->request->ro = new TestResource;
        $renderer = new ErrorRenderer;
        $this->request->ro->setRenderer($renderer);
        $this->request->ro->uri = 'nop://self/path/to/resource';
        $this->request->query = array('a' => 'koriym', 'b' => 25);
        $request = $this->request;
        $result = (string)$request;
        $this->assertSame($result, '');
    }

    public function testToStringWithoutRender()
    {
        $this->request->method = 'get';
        $this->request->ro = new TestResource;
        $this->request->ro->uri = 'nop://self/path/to/resource';
        $this->request->query = array('a' => 'koriym', 'b' => 25);
        $request = $this->request;
        $this->assertInstanceOf('\BEAR\Resource\Request', $this->request);
        $result = (string)$request;
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
        foreach ($request as $row) {
            $result[] = $row;
        }
        $expected = array(
            0 => array(
                'id' => 100,
                'title' => 'Entry1',
            ),
            1 => array(
                'id' => 101,
                'title' => 'Entry2',
            ),
            2 => array(
                'id' => 102,
                'title' => 'Entry3',
            ),
        );
        $this->assertSame($expected, $result);
    }

    /**
     * @depends request
     */
    public function testArrayAccess(Request $request)
    {
        $result = $request[100];
        $expected = array(
            'id' => 100,
            'title' => 'Entry1',
        );
        $this->assertSame($expected, $result);
    }

    /**
     * @depends request
     * @expectedException \OutOfBoundsException
     */
    public function testArrayAccessNotExists(Request $request)
    {
        $this->request->method = 'get';
        $this->request->ro = new Entry;
        $request = $this->request;
        $request[0];
    }

    /**
     * @depends request
     */
    public function testIsSet(Request $request)
    {
        $result = isset($request[100]);
        $this->assertTrue($result);
    }

    /**
     * @depends request
     */
    public function testIsSetNot(Request $request)
    {
        $this->request->method = 'get';
        $this->request->ro = new Entry;
        $this->request->query = [];
        $request = $this->request;
        $result = isset($request[0]);
        $this->assertFalse($result);
    }

    public function testWithQuery()
    {
        $this->request->set(new TestResource, 'test://self/path/to/resource', 'get', ['a' => 'koriym', 'b' => 25]);
        $this->request->withQuery(['a' => 'bear']);
        $actual = $this->request->toUriWithMethod();
        $this->assertSame('get test://self/path/to/resource?a=bear', $actual);
    }

    public function testAddQuery()
    {
        $this->request->set(new TestResource, 'test://self/path/to/resource', 'get', ['a' => 'koriym', 'b' => 25]);
        $this->request->addQuery(['a' => 'bear', 'c' => 'kuma']);
        $actual = $this->request->toUriWithMethod();
        $this->assertSame('get test://self/path/to/resource?a=bear&b=25&c=kuma', $actual);
    }
}
