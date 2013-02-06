<?php

namespace BEAR\Resource;

use Ray\Di\Definition;
use Ray\Di\Annotation;
use Ray\Di\Config;
use Ray\Di\Forge;
use Ray\Di\Container;
use Ray\Di\Manager;
use Ray\Di\Injector;
use Ray\Di\EmptyModule;
use BEAR\Resource\factory;
use Doctrine\Common\Annotations\AnnotationReader as Reader;

use BEAR\Resource\Request\Method;
use BEAR\Resource\Adapter\Nop;
use BEAR\Resource\Adapter\Test;

/**
 * Test class for BEAR.Resource.
 */
class LoggerTest extends \PHPUnit_Framework_TestCase
{
    protected $logger;

    protected function setUp()
    {
        parent::setUp();
        $this->logger = new Logger;
        $signal = require dirname(__DIR__) . '/vendor/aura/signal/scripts/instance.php';
        $this->request = new Request(new Invoker(new Config(new Annotation(new Definition, new Reader)), new Linker(new Reader), $signal));
        $this->request->method = 'get';
        $this->request->ro = new Test;
        $this->request->ro->uri = 'test://self/path/to/resource';
        $this->request->query = array('a' => 'koriym', 'b' => 25);

    }

    public function test_New()
    {
        $this->assertInstanceOf('\BEAR\Resource\Logger', $this->logger);
    }

    public function test_Log()
    {
        $test = new Test;
        $test->uri = 'test://self/path/to/resource';
        $this->request->set($test, 'test://self/path/to/resource', 'get', ['a' => 'koriym', 'b' => 25]);
        $this->logger->log($this->request, new Test);
        foreach ($this->logger as $log) {
            $this->assertSame(2, count($log));
            $this->assertSame('get test://self/path/to/resource?a=koriym&b=25', $log[0]->toUriWithMethod());
        }
    }

    public function test_count()
    {
        $this->assertSame(0, count($this->logger));
    }
}
