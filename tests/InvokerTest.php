<?php

namespace BEAR\Resource;

use Doctrine\Common\Util\Debug;

use Ray\Di\Definition,
    Ray\Di\Annotation,
    Ray\Di\Config,
    Ray\Di\Forge,
    Ray\Di\Container,
    Ray\Di\Manager,
    Ray\Di\Injector,
    Ray\Di\EmptyModule;

use Ray\Aop\Weaver,
    Ray\Aop\Bind,
    Ray\Aop\ReflectiveMethodInvocation;
use BEAR\Resource\Mock\User;

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
        $additionalAnnotations = require __DIR__ . '/scripts/additionalAnnotations.php';
        $signalProvider = function (
            $return,
            \ReflectionParameter $parameter,
            ReflectiveMethodInvocation $invovation,
            Definition $definition
        ) {
            $return->value = 1;
            return \Aura\Signal\Manager::STOP;
        };
        $config = new Config(new Annotation(new Definition, $additonalAnnotations));
        $scheme = new SchemeCollection;
        $scheme->scheme('nop')->host('self')->toAdapter(new \BEAR\Resource\Adapter\Nop);
        $scheme->scheme('prov')->host('self')->toAdapter(new \BEAR\Resource\Adapter\Prov);
        $factory = new Factory($scheme);
        $schemeAdapters = array('nop' => '\BEAR\Resource\Adapter\Nop', 'prov' => '\BEAR\Resource\Mock\Prov');
        $injector = new Injector(new Container(new Forge($config)), new EmptyModule);
        $this->signal = require dirname(__DIR__) . '/vendor/Aura.Signal/scripts/instance.php';
        $this->invoker = new Invoker($config, new Linker, $this->signal);
        $this->invoker->getSignal()->handler(
                '\BEAR\Resource\Invoker',
                \BEAR\Resource\Invoker::SIGNAL_ARGUMENT . 'Provides',
                $this->invoker->getProvidesClosure()
        );
        $this->invoker->getSignal()->handler(
                '\BEAR\Resource\Invoker',
                \BEAR\Resource\Invoker::SIGNAL_ARGUMENT . 'login_id',
                $signalProvider
        );
        $resource = new \testworld\ResourceObject\User;
        $resource->uri = 'dummy://self/User';
        $this->request = new Request($this->invoker);
        $this->request->method = 'get';
        $this->request->ro = $resource;
        $this->request->query = array('id' => 1);
    }

    public function test_Invokable()
    {
        $actual = $this->invoker->invoke($this->request);
        $expected = array('id' => 2, 'name' => 'Aramis', 'age' => 16, 'blog_id' => 12);
        $this->assertSame($actual, $expected);
    }

    public function test_InvokableWithNoPrams()
    {
        $this->request->query = array();
        $this->request->method = 'delete';
        $actual = $this->invoker->invoke($this->request);
        $expected = '1 deleted';
        $this->assertSame($actual, $expected);
    }

    public function test_InvokableMissingParam()
    {
        $this->request->query = [];
        $actual = $this->invoker->invoke($this->request);
        $expected = array('id' => 2, 'name' => 'Aramis', 'age' => 16, 'blog_id' => 12);
        $this->assertSame($actual, $expected);
    }

    public function test_InvokableDefaultParam()
    {
        $this->request->query = array();
        $this->request->method = 'post';
        $this->query = array('id' => 1);
        $actual = $this->invoker->invoke($this->request);
        $expected = 'post user[1 default_name 99]';
        $this->assertSame($actual, $expected);
    }

    /**
     * @expectedException BEAR\Resource\Exception\InvalidParameter
     */
    public function test_InvokableDefaultParamWithNoProvider()
    {
        $this->request->query = array();
        $this->request->method = 'put';
        $this->query = array();
        $actual = $this->invoker->invoke($this->request);
    }

    /**
     * @expectedException BEAR\Resource\Exception\InvalidParameter
     */
    public function test_InvokableWithNoProvider()
    {
        $this->request->ro = new Mock\Blog;
        $this->request->query = array();
        $this->request->method = 'get';
        $actual = $this->invoker->invoke($this->request);
    }

    // deprecated for @Provides any support.
//     public function test_InvokableWithUnspecificProvider()
//     {
//         $this->request->ro = new Mock\Entry;
//         $this->request->query = array();
//         $this->request->method = 'get';
//         $actual = $this->invoker->invoke($this->request);
//         $this->assertSame('entry1', $actual);
//     }

    /**
     * @expectedException BEAR\Resource\Exception\InvalidParameter
     */
    public function test_InvokableWithUnspecificProviderButNoResult()
    {
        $this->request->ro = new Mock\Comment;
        $this->request->query = array();
        $this->request->method = 'get';
        $actual = $this->invoker->invoke($this->request);
        $this->assertSame('entry1', $actual);
    }

    /**
     * @expectedException BEAR\Resource\Exception\MethodNotAllowed
     */
    public function test_InvokableInvalidMethod()
    {
        $this->request->method = 'InvalidMethod';
        $actual = $this->invoker->invoke($this->request);
        $expected = array('id' => 2, 'name' => 'Aramis', 'age' => 16);
        $this->assertSame($expected, $actual);
    }

    public function test_invokeTraversal()
    {
        $body = new \ArrayObject(array('a' => 1, 'b' => function(){
            return 2;
        }));
        $actual = $this->invoker->invokeTraversal($body);
        $expected = new \ArrayObject(array('a' =>1 ,'b' => 2));
        $this->assertSame((array)$expected, (array)$actual);
    }

    public function test_invokeWeave()
    {
        $bind = new Bind;
        $bind->bindInterceptors('onGet', array(new \testworld\Interceptor\Log));
        $weave = new Weaver(new \testworld\ResourceObject\Weave\Book, $bind);
        $this->request->ro = $weave;
        $this->request->method = 'get';
        $this->request->query = array('id' => 1);
        $actual = $this->invoker->invoke($this->request);
        $expected = "book id[1][Log] target = testworld\ResourceObject\Weave\Book, input = Array
(
    [0] => 1
)
, result = book id[1]";
        $this->assertSame($expected, $actual);
    }

    public function test_InvokableLink()
    {

        $ro = new Mock\Link;
        $this->request->ro = $ro;
        $link = new LinkType;
        $link->type = LinkType::SELF_LINK;
        $link->key = 'View';
        $links = array($link);
        $this->request->links = $links;
        $this->request->query = array('id' => 1);
        $actual = $this->invoker->invoke($this->request);
        $expected = '<html>bear1</html>';
        $this->assertSame($actual, $expected);
    }

    public function test_OptionsMethod()
    {
        $this->request->method = Invoker::OPTIONS;
        $response = $this->invoker->invoke($this->request);
        $actual = $response['allows'];
        $expected = array('Get', 'Post', 'Put', 'Delete');
        asort($actual);
        asort($expected);
        $this->assertSame($actual, $expected);
    }

    public function test_OptionsMethod2()
    {
        $this->request->method = Invoker::OPTIONS;
        $this->request->ro = new  \testworld\ResourceObject\RestBucks\Order;
        $response = $this->invoker->invoke($this->request);
        $actual = $response['allows'];
        $expected = array('Get', 'Post');
        asort($actual);
        asort($expected);
        $this->assertSame($actual, $expected);
    }

    public function test_annotationMethod()
    {
        $this->request->ro = new \testworld\ResourceObject\MethodAnnotation;
        $actual = $this->invoker->invoke($this->request);
        $expected = 'get 1';
        $this->assertSame($actual, $expected);
    }
}