<?php

namespace BEAR\Resource;

use Ray\Di\Definition;
use Ray\Di\Annotation;
use Ray\Di\Config;
use Ray\Di\Injector;
use Ray\Aop\Weaver;
use Ray\Aop\Bind;
use Ray\Aop\ReflectiveMethodInvocation;
use Doctrine\Common\Annotations\AnnotationReader as Reader;
use Aura\Signal\Manager;
use testworld\Interceptor\Log;
use testworld\ResourceObject\RestBucks\Order;
use testworld\ResourceObject\User;
use testworld\ResourceObject\Weave\Book;
use testworld\ResourceObject\Weave\Link;

/**
 * Test class for BEAR.Resource.
 */
class InvokerTest extends \PHPUnit_Framework_TestCase
{
    protected $signal;
    protected $invoker;
    protected $request;
    protected $query;

    protected function setUp()
    {
        $signal = require dirname(__DIR__) . '/vendor/aura/signal/scripts/instance.php';
        $signal->handler(
            '\BEAR\Resource\ReflectiveParams',
            ReflectiveParams::SIGNAL_PARAM . 'Provides',
            new SignalHandler\Provides
        );
        $signal->handler(
            '\BEAR\Resource\ReflectiveParams',
            ReflectiveParams::SIGNAL_PARAM . 'login_id',
            function (
                $return,
                \ReflectionParameter $parameter,
                ReflectiveMethodInvocation $invocation,
                Definition $definition
            ) {
                $return->value = 1;

                return Manager::STOP;
            }
        );
        $this->invoker = new Invoker(new Linker(new Reader), new ReflectiveParams(new Config(new Annotation(new Definition, new Reader)), $signal));

        $resource = new User;
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
        $this->request->query = ['id' => 1];
        $actual = $this->invoker->invoke($this->request)->body;
        $expected = 'post user[1 default_name 99]';
        $this->assertSame($actual, $expected);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\Parameter
     */
    public function test_InvokerInterfaceDefaultParamWithNoProvider()
    {
        $this->request->query = [];
        $this->request->method = 'put';
        $this->query = [];
        $this->invoker->invoke($this->request);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\Parameter
     */
    public function test_InvokerInterfaceWithNoProvider()
    {
        $this->request->ro = new Mock\Blog;
        $this->request->query = [];
        $this->request->method = 'get';
        $this->invoker->invoke($this->request);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\Parameter
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
     * @expectedException \BEAR\Resource\Exception\MethodNotAllowed
     */
    public function test_InvokerInterfaceInvalidMethod()
    {
        $this->request->method = 'InvalidMethod';
        $this->invoker->invoke($this->request);
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
        $bind->bindInterceptors('onGet', [new Log]);
        $weave = new Weaver(new Book, $bind);
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
        $bind->bindInterceptors('onGet', [new Log]);
        $weave = new Weaver(new Link, $bind);
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
        $this->request->ro = new  Order;
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
        $this->request->ro = new Weaver(new Order, new Bind);
        $response = $this->invoker->invoke($this->request);
        $actual = $response->headers['allow'];
        $expected = ['get', 'post'];
        asort($actual);
        asort($expected);
        $this->assertSame($actual, $expected);
    }

}
