<?php

namespace BEAR\Resource;

use Ray\Di\Definition,
Ray\Di\Annotation,
Ray\Di\Config,
Ray\Di\Forge,
Ray\Di\Container,
Ray\Di\Manager,
Ray\Di\Injector,
Ray\Di\EmptyModule,
BEAR\Resource\factory;
use Doctrine\Common\Annotations\AnnotationReader as Reader;

use BEAR\Resource\Request\Method,
BEAR\Resource\Adapter\Nop,
BEAR\Resource\Adapter\Test;
use DateTime;

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
        $signal = require dirname(__DIR__) . '/vendor/Aura/Signal/scripts/instance.php';
        $this->request = new Request(new Invoker(new Config(new Annotation(new Definition)), new Linker(new Reader), $signal));
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
        $this->logger->log($this->request, 0);
        foreach($this->logger as $log) {
            $this->assertSame(2, count($log));
            $this->assertSame('get test://self/path/to/resource?a=koriym&b=25', $log[0]->toUriWithMethod());
        }
    }
}
