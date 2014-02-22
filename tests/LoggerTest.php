<?php

namespace BEAR\Resource;

use Aura\Signal\HandlerFactory;
use Aura\Signal\Manager;
use Aura\Signal\ResultCollection;
use Aura\Signal\ResultFactory;
use BEAR\Resource\Adapter\Nop;
use BEAR\Resource\Adapter\Test;
use BEAR\Resource\Adapter\TestResource;
use Doctrine\Common\Annotations\AnnotationReader as Reader;
use Ray\Di\Definition;
use Ray\Di\Injector;

class TestWriter implements LogWriterInterface
{
    public function write(RequestInterface $req, ResourceObject $result)
    {
    }
}

class LoggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Request
     */
    protected $request;

    protected function setUp()
    {
        parent::setUp();
        $this->logger = new Logger;

        $signal = new Manager(new HandlerFactory, new ResultFactory, new ResultCollection);
        $params = new NamedParameter(new SignalParameter($signal, new Param));
        $invoker = new Invoker(new Linker(new Reader), $params);

        $this->request = new Request($invoker);
        $this->request->method = 'get';
        $this->request->ro = new TestResource;
        $this->request->ro->uri = 'test://self/path/to/resource';
        $this->request->query = array('a' => 'koriym', 'b' => 25);
    }

    public function testNew()
    {
        $this->assertInstanceOf('\BEAR\Resource\Logger', $this->logger);
    }

    public function testLog()
    {
        $test = new TestResource;
        $test->uri = 'test://self/path/to/resource';
        $this->request->set($test, 'test://self/path/to/resource', 'get', ['a' => 'koriym', 'b' => 25]);
        $this->logger->log($this->request, new TestResource);
        foreach ($this->logger as $log) {
            $this->assertSame(2, count($log));
            $request = $log[0];
            /** @var $request \BEAR\Resource\Request */
            $this->assertSame('get test://self/path/to/resource?a=koriym&b=25', $request->toUriWithMethod());
        }
    }

    public function testSetWriter()
    {
        $this->logger->setWriter(new TestWriter);
        $this->request->set(new TestResource, 'test://self/path/to/resource', 'get', ['a' => 'koriym', 'b' => 25]);
        $this->logger->log($this->request, new TestResource);
        $result = $this->logger->write($this->request, new TestResource);
        $this->assertSame(true, $result);
    }

    public function testSetWriter_WhenWriterIsNotSet()
    {
        $result = $this->logger->write($this->request, new TestResource);
        $this->assertSame(false, $result);
    }

    public function testCount()
    {
        $this->assertSame(0, count($this->logger));
    }

    public function testSerialize()
    {
        $request = $this->request;
        $request->closure = function(){};
        $this->logger->log($request, new TestResource);
        $logStr = serialize($this->logger);
        $this->assertInternalType('string', $logStr);
        return $logStr;
    }

    /**
     * @param $logStr
     *
     * @depends testSerialize
     */
    public function testUnserialize($logStr)
    {
        $logger = unserialize($logStr);
        $this->assertInstanceOf('BEAR\Resource\Logger', $logger);
    }
}
