<?php

namespace BEAR\Resource;

use BEAR\Resource\DevInvoker;

use Aura\Signal\Manager;
use Aura\Signal\HandlerFactory;
use Aura\Signal\ResultFactory;
use Aura\Signal\ResultCollection;
use Ray\Di\Definition;
use Ray\Di\Injector;
use Ray\Aop\Weaver;
use Ray\Aop\Bind;
use Doctrine\Common\Annotations\AnnotationReader as Reader;
use testworld\Interceptor\Log;
use testworld\ResourceObject\User;
use testworld\ResourceObject\Weave\Book;

class DevInvokerTest extends \PHPUnit_Framework_TestCase
{
    protected $signal;
    protected $invoker;

    protected function setUp()
    {
        $signal = new Manager(new HandlerFactory, new ResultFactory, new ResultCollection);
        $params = new NamedParams(new SignalParam($signal, new Param));
        $this->invoker = new DevInvoker(new Linker(new Reader), $params);


        $resource = new User;
        $resource->uri = 'dummy://self/User';
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
    public function test_isHEADER_EXECUTION_TIME_headerExists(array $headers)
    {
        $this->assertArrayHasKey(DevInvoker::HEADER_EXECUTION_TIME, $headers);
    }

    /**
     * @depends invoke
     */
    public function test_HEADER_EXECUTION_TIME_isPlus(array $headers)
    {
        $this->assertTrue($headers[DevInvoker::HEADER_EXECUTION_TIME] > 0);
    }

    /**
     * @depends invoke
     */
    public function test_isHEADER_MEMORY_USAGEExists(array $headers)
    {
        $this->assertArrayHasKey(DevInvoker::HEADER_MEMORY_USAGE, $headers);
    }

    /**
     * @depends invoke
     */
    public function test_HEADER_MEMORY_USAGE_isPlus(array $headers)
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
        $weave = new Weaver(new Book, $bind);
        $this->request->ro = $weave;
        $this->request->method = 'get';
        $this->request->query = ['id' => 1];
        $actual = $this->invoker->invoke($this->request);

        $ro = $this->request->ro->___getObject();
        $this->assertInstanceOf('testworld\ResourceObject\Weave\Book', $ro);

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
        $bind = (array)$headers[DevInvoker::HEADER_INTERCEPTORS];
        $this->assertSame(
            json_encode(['onGet' => ['testworld\Interceptor\Log']]),
            $headers[DevInvoker::HEADER_INTERCEPTORS]
        );
    }
}
