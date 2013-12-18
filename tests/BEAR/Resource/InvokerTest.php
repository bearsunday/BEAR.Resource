<?php

namespace BEAR\Resource;

use Aura\Signal\Manager;
use Aura\Signal\HandlerFactory;
use Aura\Signal\ResultFactory;
use Aura\Signal\ResultCollection;
use Ray\Aop\Compiler;
use Ray\Di\Definition;
use Ray\Di\Injector;
use Ray\Aop\Bind;
use Doctrine\Common\Annotations\AnnotationReader as Reader;
use BEAR\Resource\Interceptor\Log;
use TestVendor\Sandbox\Resource\App\Link;
use TestVendor\Sandbox\Resource\App\Restbucks\Order;
use TestVendor\Sandbox\Resource\App\User;
use TestVendor\Sandbox\Resource\App\Param\User as ParamUser;

/**
 * Test class for BEAR.Resource.
 */
class InvokerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Invoker
     */
    protected $invoker;

    /**
     * @var array
     */
    protected $query = [];

    /**
     * @var Request
     */
    protected $request;

    protected function setUp()
    {
        $signal = new Manager(new HandlerFactory, new ResultFactory, new ResultCollection);
        $params = new NamedParameter(new SignalParameter($signal, new Param));
        $this->invoker = new Invoker(new Linker(new Reader), $params);

        $resource = new User;
        $resource->uri = 'dummy://self/User';
        $this->request = new Request($this->invoker);
        $this->request->method = 'get';
        $this->request->ro = $resource;
        $this->request->query = ['id' => 1];
    }

    public function testInvoke()
    {
        $actual = $this->invoker->invoke($this->request)->body;
        $expected = ['id' => 2, 'name' => 'Aramis', 'age' => 16, 'blog_id' => 12];
        $this->assertSame($actual, $expected);
    }

    public function testInvokerInterfaceDefaultParam()
    {
        $this->request->query = [];
        $this->request->method = 'post';
        $this->request->query = ['id' => 1];
        $actual = $this->invoker->invoke($this->request)->body;
        $expected = 'post user[1 default_name 99]';
        $this->assertSame($actual, $expected);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\Parameter
     */
    public function testInvokerInterfaceDefaultParamWithNoProvider()
    {
        $this->request->query = [];
        $this->request->method = 'put';
        $this->query = [];
        $this->invoker->invoke($this->request);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\Parameter
     */
    public function testInvokerInterfaceWithNoProvider()
    {
        $this->request->ro = new Mock\Blog;
        $this->request->query = [];
        $this->request->method = 'get';
        $this->invoker->invoke($this->request);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\Parameter
     */
    public function testInvokerInterfaceWithUnspecificProviderButNoResult()
    {
        $this->request->ro = new Mock\Comment;
        $this->request->query = [];
        $this->request->method = 'get';
        $actual = $this->invoker->invoke($this->request);
        $this->assertSame('entry1', $actual);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\MethodNotAllowed
     */
    public function testInvokerInterfaceInvalidMethod()
    {
        $this->request->method = 'InvalidMethod';
        $this->invoker->invoke($this->request);
    }

    public function testInvokeTraversal()
    {
        $body = new \ArrayObject([
            'a' => 1,
            'b' => function () {
                return 2;
            }
        ]);
        $actual = $this->invoker->invokeTraversal($body);
        $expected = new \ArrayObject(['a' => 1, 'b' => 2]);
        $this->assertSame((array)$expected, (array)$actual);
    }

    public function testInvokeWeave()
    {
        $bind = new Bind;
        $bind->bindInterceptors('onGet', [new Log]);
        $weave = $GLOBALS['COMPILER']->newInstance('TestVendor\Sandbox\Resource\App\Weave\Book', [], $bind);
        $this->request->ro = $weave;
        $this->request->method = 'get';
        $this->request->query = ['id' => 1];
        $actual = $this->invoker->invoke($this->request)->body;
        $expected = "book id[1][Log] target = TestVendor\\Sandbox\\Resource\\App\\Weave\\Book, input = Array\n(\n    [0] => 1\n)\n, result = book id[1]";
        $this->assertSame($expected, $actual);
    }

    public function testOptionsMethod()
    {
        $this->request->method = Invoker::OPTIONS;
        $response = $this->invoker->invoke($this->request);
        $actual = $response->headers['allow'];
        $expected = ['get', 'post', 'put', 'delete'];
        asort($actual);
        asort($expected);
        $this->assertSame($actual, $expected);
    }

    public function testOptionsMethod2()
    {
        $this->request->method = Invoker::OPTIONS;
        $this->request->ro = new  Order;
        $response = $this->invoker->invoke($this->request);
        $actual = $response->headers['allow'];
        $expected = ['get', 'post'];
        asort($actual);
        asort($expected);
        $this->assertSame($actual, $expected);
    }

    public function testOptionsWeaver()
    {
        $this->request->method = Invoker::OPTIONS;
        $this->request->ro = $GLOBALS['COMPILER']->newInstance('TestVendor\Sandbox\Resource\App\RestBucks\Order', [], new Bind);

        $response = $this->invoker->invoke($this->request);
        $actual = $response->headers['allow'];
        $expected = ['get', 'post'];
        asort($actual);
        asort($expected);
        $this->assertSame($actual, $expected);
    }

    public function testSetResourceLogger()
    {
        $invoker = $this->invoker->setResourceLogger(new Logger);
        $this->assertInstanceOf('BEAR\Resource\Invoker', $invoker);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvokeExceptionHandle()
    {
        $outOfRangeId = 4;
        $this->request->query = ['id' => $outOfRangeId];
        $this->invoker->invoke($this->request)->body;
    }

    /**
     * @expectedException \BEAR\Resource\Exception\ParameterInService
     */
    public function testInvokeExceptionHandleHead()
    {
        $this->request->ro = new ParamUser;
        $this->request->query = ['author_id' => ParamUser::PARAMETER_IN_SERVICE_EXCEPTION];
        $this->request->method = 'head';
        $this->invoker->invoke($this->request)->body;
    }
}
