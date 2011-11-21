<?php

namespace BEAR\Resource;

use Ray\Aop\Weaver,
    Ray\Aop\Bind;

use BEAR\Resource\Mock\User,
    BEAR\Resource\Invoker;

use Ray\Di\Annotation,
    Ray\Di\Config,
    Ray\Di\Forge,
    Ray\Di\Container,
    Ray\Di\Manager,
    Ray\Di\Injector,
    Ray\Di\EmptyModule;

/**
 * Test class for PHP.Skelton.
 */
class InvokerTest extends \PHPUnit_Framework_TestCase
{
    protected $invoker;

    protected function setUp()
    {
        parent::setUp();
        $config = new Config(new Annotation);
        $schemeAdapters = array('nop' => '\BEAR\Resource\Adapter\Nop', 'prov' => '\BEAR\Resource\Mock\Prov');
        $injector = new Injector(new Container(new Forge($config)), new EmptyModule());
        $this->invoker = new Invoker($config, new Linker);

        $factory = new Factory($injector, $schemeAdapters);
        $resource = new \testworld\ResourceObject\User;
        $resource->uri = 'dummy://self/User';
        $this->request = new Request($this->invoker);
        $this->request->method = 'get';
        $this->request->ro = $resource;
        $this->request->query = array('id' => 1);
    }

    public function test_Invoke()
    {
        $actual = $this->invoker->invoke($this->request);
        $expected = array('id' => 2, 'name' => 'Aramis', 'age' => 16, 'blog_id' => 12);
        $this->assertSame($actual, $expected);
    }

    public function test_InvokeWithNoPrams()
    {
        $this->request->query = array();
        $this->request->method = 'delete';
        $actual = $this->invoker->invoke($this->request);
        $expected = 'deleted';
        $this->assertSame($actual, $expected);
    }

    public function test_InvokeMissingParam()
    {
        $this->request->query = array();
        $actual = $this->invoker->invoke($this->request);
        $expected = array('id' => 2, 'name' => 'Aramis', 'age' => 16, 'blog_id' => 12);
        $this->assertSame($actual, $expected);
    }

    public function test_InvokeDefaultParam()
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
    public function test_InvokeDefaultParamWithNoProvider()
    {
        $this->request->query = array();
        $this->request->method = 'put';
        $this->query = array();
        $actual = $this->invoker->invoke($this->request);
    }

    /**
     * @expectedException BEAR\Resource\Exception\InvalidParameter
     */
    public function test_InvokeWithNoProvider()
    {
        $this->request->ro = new Mock\Blog;
        $this->request->query = array();
        $this->request->method = 'get';
        $actual = $this->invoker->invoke($this->request);
    }

    public function test_InvokeWithUnspecificProvider()
    {
        $this->request->ro = new Mock\Entry;
        $this->request->query = array();
        $this->request->method = 'get';
        $actual = $this->invoker->invoke($this->request);
        $this->assertSame('entry1', $actual);
    }

    /**
     * @expectedException BEAR\Resource\Exception\InvalidParameter
     */
    public function test_InvokeWithUnspecificProviderButNoResult()
    {
        $this->request->ro = new Mock\Comment;
        $this->request->query = array();
        $this->request->method = 'get';
        $actual = $this->invoker->invoke($this->request);
        $this->assertSame('entry1', $actual);
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function test_InvokeInvalidMethod()
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

    public function test_InvokeLink()
    {

        $ro = new Mock\Link;
        $this->request->ro = $ro;
        $link = new Link;
        $link->type = Link::SELF_LINK;
        $link->key = 'View';
        $links = array($link);
        $this->request->links = $links;
        $this->request->query = array('id' => 1);
        $actual = $this->invoker->invoke($this->request);
        $expected = '<html>bear1</html>';
        $this->assertSame($actual, $expected);
    }

}