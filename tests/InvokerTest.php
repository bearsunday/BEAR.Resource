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

use Ray\Aop\Weaver;
use Ray\Aop\Bind;
use Ray\Aop\ReflectiveMethodInvocation;
use BEAR\Resource\Mock\User;
use Doctrine\Common\Annotations\AnnotationReader as Reader;

/**
 * Test class for BEAR.Resource.
 */
class InvokerTest extends \PHPUnit_Framework_TestCase
{
    protected $signal;
    protected $invoker;

    protected function setUp()
    {
        parent::setUp();
        $signalProvider = function (
            $return,
            \ReflectionParameter $parameter,
            ReflectiveMethodInvocation $invovation,
            Definition $definition
        ) {
            $return->value = 1;

            return \Aura\Signal\Manager::STOP;
        };
        $config = new Config(new Annotation(new Definition, new Reader));
        $scheme = new SchemeCollection;
        $scheme->scheme('nop')->host('self')->toAdapter(new \BEAR\Resource\Adapter\Nop);
        $scheme->scheme('prov')->host('self')->toAdapter(new \BEAR\Resource\Adapter\Prov);
        $factory = new Factory($scheme);
        $schemeAdapters = ['nop' => '\BEAR\Resource\Adapter\Nop', 'prov' => '\BEAR\Resource\Mock\Prov'];
        $injector = new Injector(new Container(new Forge($config)), new EmptyModule);
        $this->signal = require dirname(__DIR__) . '/vendor/aura/signal/scripts/instance.php';
        $this->invoker = new Invoker($config, new Linker(new Reader), $this->signal);
        $this->invoker->getSignal()->handler(
            '\BEAR\Resource\Invoker',
            \BEAR\Resource\Invoker::SIGNAL_PARAM . 'Provides',
            new SignalHandler\Provides
        );
        $this->invoker->getSignal()->handler(
            '\BEAR\Resource\Invoker',
            \BEAR\Resource\Invoker::SIGNAL_PARAM . 'login_id',
            $signalProvider
        );
        $resource = new \testworld\ResourceObject\User;
        $resource->uri = 'dummy://self/User';
        $this->request = new Request($this->invoker);
        $this->request->method = 'get';
        $this->request->ro = $resource;
        $this->request->query = ['id' => 1];
    }

    public function test_Invoke()
    {
        $actual = $this->invoker->invoke($this->request)->body;
        $expected = ['id' => 2, 'name' => 'Aramis', 'age' => 16, 'blog_id' => 12];
        $this->assertSame($actual, $expected);
    }

    public function test_InvokerInterfaceWithNoPrams()
    {
        $this->request->query = [];
        $this->request->method = 'delete';
        $actual = $this->invoker->invoke($this->request)->body;
        $expected = '1 deleted';
        $this->assertSame($actual, $expected);
    }

    public function test_InvokerInterfaceMissingParam()
    {
        $this->request->query = [];
        $actual = $this->invoker->invoke($this->request)->body;
        $expected = ['id' => 2, 'name' => 'Aramis', 'age' => 16, 'blog_id' => 12];
        $this->assertSame($actual, $expected);
    }

    public function test_InvokerInterfaceDefaultParam()
    {
        $this->request->query = [];
        $this->request->method = 'post';
        $this->query = ['id' => 1];
        $actual = $this->invoker->invoke($this->request)->body;
        $expected = 'post user[1 default_name 99]';
        $this->assertSame($actual, $expected);
    }

    /**
     * @expectedException BEAR\Resource\Exception\Parameter
     */
    public function test_InvokerInterfaceDefaultParamWithNoProvider()
    {
        $this->request->query = [];
        $this->request->method = 'put';
        $this->query = [];
        $actual = $this->invoker->invoke($this->request);
    }

    /**
     * @expectedException BEAR\Resource\Exception\Parameter
     */
    public function test_InvokerInterfaceWithNoProvider()
    {
        $this->request->ro = new Mock\Blog;
        $this->request->query = [];
        $this->request->method = 'get';
        $actual = $this->invoker->invoke($this->request);
    }

    // deprecated for @Provides any support.
    //     public function test_InvokerInterfaceWithUnspecificProvider()
    //     {
    //         $this->request->ro = new Mock\Entry;
    //         $this->request->query = [];
    //         $this->request->method = 'get';
    //         $actual = $this->invoker->invoke($this->request);
    //         $this->assertSame('entry1', $actual);
    //     }

    /**
     * @expectedException BEAR\Resource\Exception\Parameter
     */
    public function test_InvokerInterfaceWithUnspecificProviderButNoResult()
    {
        $this->request->ro = new Mock\Comment;
        $this->request->query = [];
        $this->request->method = 'get';
        $actual = $this->invoker->invoke($this->request);
        $this->assertSame('entry1', $actual);
    }

    /**
     * @expectedException BEAR\Resource\Exception\MethodNotAllowed
     */
    public function test_InvokerInterfaceInvalidMethod()
    {
        $this->request->method = 'InvalidMethod';
        $actual = $this->invoker->invoke($this->request);
    }

    public function test_invokeTraversal()
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

    public function test_invokeWeave()
    {
        $bind = new Bind;
        $bind->bindInterceptors('onGet', [new \testworld\Interceptor\Log]);
        $weave = new Weaver(new \testworld\ResourceObject\Weave\Book, $bind);
        $this->request->ro = $weave;
        $this->request->method = 'get';
        $this->request->query = ['id' => 1];
        $actual = $this->invoker->invoke($this->request)->body;
        $expected = "book id[1][Log] target = testworld\\ResourceObject\\Weave\\Book, input = Array\n(\n    [0] => 1\n)\n, result = book id[1]";
        $this->assertSame($expected, $actual);
    }

    public function test_invokeWeaveWithLink()
    {
        $bind = new Bind;
        $bind->bindInterceptors('onGet', [new \testworld\Interceptor\Log]);
        $weave = new Weaver(new \testworld\ResourceObject\Weave\Link, $bind);
        $this->request->ro = $weave;
        $this->request->method = 'get';
        $this->request->query = ['animal' => 'bear'];
        $link = new LinkType;
        $link->type = LinkType::SELF_LINK;
        $link->key = 'View';
        $links = [$link];
        $this->request->links = $links;
        $actual = $this->invoker->invoke($this->request)->body;
        $expected = "<html>Like a bear to a honey pot.[Log] target = testworld\\ResourceObject\\Weave\\Link, input = Array\n(\n    [0] => bear\n)\n, result = Like a bear to a honey pot.</html>";
        $this->assertSame($expected, $actual);
    }

    public function test_InvokerInterfaceLink()
    {

        $ro = new Mock\Link;
        $this->request->ro = $ro;
        $link = new LinkType;
        $link->type = LinkType::SELF_LINK;
        $link->key = 'View';
        $links = [$link];
        $this->request->links = $links;
        $this->request->query = ['id' => 1];
        $actual = $this->invoker->invoke($this->request)->body;
        $expected = '<html>bear1</html>';
        $this->assertSame($actual, $expected);
    }

    public function test_OptionsMethod()
    {
        $this->request->method = Invoker::OPTIONS;
        $response = $this->invoker->invoke($this->request);
        $actual = $response->headers['allow'];
        $expected = ['get', 'post', 'put', 'delete'];
        asort($actual);
        asort($expected);
        $this->assertSame($actual, $expected);
    }

    public function test_OptionsMethod2()
    {
        $this->request->method = Invoker::OPTIONS;
        $this->request->ro = new  \testworld\ResourceObject\RestBucks\Order;
        $response = $this->invoker->invoke($this->request);
        $actual = $response->headers['allow'];
        $expected = ['get', 'post'];
        asort($actual);
        asort($expected);
        $this->assertSame($actual, $expected);
    }

    public function test_OptionsWeaver()
    {
        $this->request->method = Invoker::OPTIONS;
        $this->request->ro = new Weaver(new  \testworld\ResourceObject\RestBucks\Order, new Bind);
        $response = $this->invoker->invoke($this->request);
        $actual = $response->headers['allow'];
        $expected = ['get', 'post'];
        asort($actual);
        asort($expected);
        $this->assertSame($actual, $expected);
    }

    public function test_getConfig()
    {
        $actual = $this->invoker->getConfig();
        $this->assertInstanceOf('Ray\Di\Config', $actual);
    }

    public function test_getParams()
    {
        $ro = new  \testworld\ResourceObject\RestBucks\Order;
        $query = ['id' => 1];
        $actual = $this->invoker->getParams($ro, 'onGet', $query);
        $expected = [1];
        $this->assertSame($expected, $actual);
    }

    public function test_getParamsInRandomOrder()
    {
        $ro = new  \testworld\ResourceObject\RestBucks\Payment;
        // in any order
        $query = [
            'credit_card_number' => '12345678',
            'order_id' => 1,
            'name' => 'koriym',
            'expires' => "20130214",
            'amount' => 1
        ];
        $actual = $this->invoker->getParams($ro, 'onPut', $query);
        $expected = [
            1,
            '12345678',
            '20130214',
            'koriym',
            1
        ];
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\MethodNotAllowed
     */
    public function test_getParamsMethodNotExists()
    {
        $ro = new  \testworld\ResourceObject\RestBucks\Order;
        $query = ['id' => 1];
        $actual = $this->invoker->getParams($ro, 'onDelete', $query);
    }
}
