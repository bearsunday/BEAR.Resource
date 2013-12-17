<?php

namespace BEAR\Resource;

use Aura\Signal\Manager;
use Aura\Signal\HandlerFactory;
use Aura\Signal\ResultFactory;
use Aura\Signal\ResultCollection;
use Ray\Aop\Compiler;
use Ray\Di\Definition;
use Ray\Aop\Bind;
use Doctrine\Common\Annotations\AnnotationReader as Reader;
use BEAR\Resource\Interceptor\Log;
use Sandbox\Resource\App\User;

class DevInvokerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  \Aura\Signal\Manager
     */
    protected $signal;

    /**
     * @var Invoker
     */
    private $invoker;

    /**
     * @var Request
     */
    private $request;

    protected function setUp()
    {
        $signal = new Manager(new HandlerFactory, new ResultFactory, new ResultCollection);
        $params = new NamedParameter(new SignalParameter($signal, new Param));
        $this->invoker = new DevInvoker(new Linker(new Reader), $params);


        $resource = new User;
        $resource->uri = 'dummy://self/user';
        $this->request = new Request($this->invoker);
        $this->request->method = 'get';
        $this->request->ro = $resource;
        $this->request->query = ['id' => 1];
    }

    /**
     * @test
     */
    public function invoke()
    {
        $actual = $this->invoker->invoke($this->request)->body;
        $expected = ['id' => 2, 'name' => 'Aramis', 'age' => 16, 'blog_id' => 12];
        $ro = $this->request->ro;
        $this->assertSame($actual, $expected);

        return $ro->headers;
    }

    /**
     * @depends invoke
     */
    public function testIsHEADER_EXECUTION_TIME_headerExists(array $headers)
    {
        $this->assertArrayHasKey(DevInvoker::HEADER_EXECUTION_TIME, $headers);
    }

    /**
     * @depends invoke
     */
    public function testIsHEADER_EXECUTION_TIME_Plus(array $headers)
    {
        $this->assertTrue($headers[DevInvoker::HEADER_EXECUTION_TIME] > 0);
    }

    /**
     * @depends invoke
     */
    public function testIsHEADER_MEMORY_USAGEExists(array $headers)
    {
        $this->assertArrayHasKey(DevInvoker::HEADER_MEMORY_USAGE, $headers);
    }

    /**
     * @depends invoke
     */
    public function testIsHEADER_MEMORY_USAGE_Plus(array $headers)
    {
        $this->assertTrue($headers[DevInvoker::HEADER_MEMORY_USAGE] > 0);
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.InvokerTest::test_invokeWeave()
     *
     * @test
     */
    public function invokeWeave()
    {
        $bind = new Bind;
        $bind->bindInterceptors('onGet', [new Log]);
        $weave = $GLOBALS['COMPILER']->newInstance('Sandbox\Resource\App\Weave\Book', [], $bind);
        $this->request->ro = $weave;
        $this->request->method = 'get';
        $this->request->query = ['id' => 1];
        $this->invoker->invoke($this->request);

        $ro = $this->request->ro;
        $this->assertInstanceOf('Sandbox\Resource\App\Weave\Book', $ro);

        return $ro->headers;
    }

    /**
     * @param array $headers
     *
     * @depends invokeWeave
     */
    public function testInvokeWeavedResourceHasLogInObjectHeader(array $headers)
    {
        $this->assertArrayHasKey(DevInvoker::HEADER_INTERCEPTORS, $headers);
    }

    /**
     * @param array $headers
     *
     * @depends invokeWeave
     */
    public function testInvokeWeavedResourceLogInObjectHeaderIsBind(array $headers)
    {
        $this->assertTrue(isset($headers[DevInvoker::HEADER_INTERCEPTORS]));
    }

    /**
     * @param array $headers
     *
     * @depends invokeWeave
     */
    public function testInvokeWeavedResourceLogInObjectHeaderContents(array $headers)
    {
        /** @noinspection PhpExpressionResultUnusedInspection */
        (array)$headers[DevInvoker::HEADER_INTERCEPTORS];
        $this->assertSame(
            json_encode(['onGet' => ['BEAR\Resource\Interceptor\Log']]),
            $headers[DevInvoker::HEADER_INTERCEPTORS]
        );
    }

    /**
     * @expectedException \BEAR\Resource\Exception\MethodNotAllowed
     */
    public function testInvokerInterfaceInvalidMethod()
    {
        $this->request->method = 'InvalidMethod';
        $this->invoker->invoke($this->request);
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
}
