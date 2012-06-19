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
    BEAR\Resource\Builder,
    BEAR\Resource\Mock\User;
use Ray\Aop\ReflectiveMethodInvocation;
use BEAR\Resource\SignalHandler\Provides;
use Guzzle\Common\Cache\DoctrineCacheAdapter as CacheAdapter;
use Doctrine\Common\Cache\ArrayCache as Cache;

/**
 * Test class for BEAR.Resource.
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    protected $skelton;

    /**
     * @var Aura\Signal\Manager
     */
    protected $singnal;

    protected function setUp()
    {
        parent::setUp();
        $additionalAnnotations = require __DIR__ . '/scripts/additionalAnnotations.php';
        $injector = new Injector(new Container(new Forge(new Config(new Annotation(new Definition)))), new EmptyModule);
        $scheme = new SchemeCollection;
        $scheme->scheme('app')->host('self')->toAdapter(new \BEAR\Resource\Adapter\App($injector, 'testworld', 'ResourceObject'));
        $scheme->scheme('page')->host('self')->toAdapter(new \BEAR\Resource\Adapter\App($injector, 'testworld', 'Page'));
        $scheme->scheme('nop')->host('self')->toAdapter(new \BEAR\Resource\Adapter\Nop);
        $scheme->scheme('test')->host('self')->toAdapter(new \BEAR\Resource\Adapter\Test);
        $scheme->scheme('prov')->host('self')->toAdapter(new \BEAR\Resource\Adapter\Prov);
        $scheme->scheme('http')->host('*')->toAdapter(new \BEAR\Resource\Adapter\Http);
        $this->factory = new Factory($scheme);
        $factory = new Factory($scheme);
        $this->signal = require dirname(__DIR__) . '/vendor/Aura/Signal/scripts/instance.php';
        $this->invoker = new Invoker(new Config(new Annotation(new Definition), $additonalAnnotations), new Linker, $this->signal);
        $this->resource = new Resource($factory, $this->invoker, new Request($this->invoker));
        $this->user = $factory->newInstance('app://self/user');
        $this->nop = $factory->newInstance('nop://self/dummy');
        $this->query = array(
            'id' => 10,
            'name' => 'Ray',
            'age' => 43
        );
    }

    public function test_New()
    {
        $this->assertInstanceOf('\BEAR\Resource\Resource', $this->resource);
    }

    /**
     * @expectedException BEAR\Resource\Exception
     */
    public function test_Exception()
    {
        throw new Exception;
    }

    public function test_newInstanceNop()
    {
        $instance = $this->resource->newInstance('nop://self/path/to/dummy');
        $this->assertInstanceOf('\BEAR\Resource\Adapter\Nop', $instance);
    }

    public function test_newInstanceAppWithProvider()
    {
        $instance = $this->resource->newInstance('prov://self/path/to/dummy');
        $this->assertInstanceOf('\stdClass', $instance);
    }


    public function testGetRequestByPost()
    {
        $query = array();
        $request = $this->resource->get->object($this->nop)->withQuery($query)->request();
        $this->assertInstanceOf('\BEAR\Resource\Request', $request);
    }

    public function test_get()
    {
        $request = $this->resource->get->object($this->nop)->withQuery($this->query)->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function test_post()
    {
        $request = $this->resource->post->object($this->nop)->withQuery($this->query)->request();
        $expected = "post nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function test_postPoeCsrf()
    {
        $request = $this->resource->post->object($this->nop)->withQuery($this->query)->poe->csrf->request();
        $expected = "post nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    /**
     * @expectedException BEAR\Resource\Exception\BadRequest
     */
    public function test_postInvalidOption()
    {
        $request = $this->resource->post->object($this->nop)->withQuery($this->query)->poe->csrf->invalid_option_cause_exception->request();
        $expected = "post nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function test_put()
    {
        $request = $this->resource->put->object($this->nop)->withQuery($this->query)->request();
        $expected = "put nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function test_delete()
    {
        $request = $this->resource->delete->object($this->nop)->withQuery($this->query)->request();
        $expected = "delete nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function test_linkSelfString()
    {
        $request = $this->resource->get->object($this->nop)->withQuery($this->query)->linkSelf('dummyLink')->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43, link self:dummyLink";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function test_linkNewString()
    {
        $request = $this->resource->get->object($this->nop)->withQuery($this->query)->linkNew('dummyLink')->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43, link new:dummyLink";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function test_linkCrawlString()
    {
        $request = $this->resource->get->object($this->nop)->withQuery($this->query)->linkCrawl('dummyLink')->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43, link crawl:dummyLink";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function test_linkTwo()
    {
        $request = $this->resource->get->object($this->nop)->withQuery($this->query)->linkSelf('dummyLink')->linkSelf('dummyLink2')->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43, link self:dummyLink, link self:dummyLink2";
        $this->assertSame($expected, $request->toUriWithMethod());
    }


    public function testPostWithNoDefaultParameter()
    {
        $actual = $this->resource->post->object($this->user)->withQuery($this->query)->eager->request();
        $expected = "post user[10 Ray 43]";
        $this->assertSame($expected, $actual->body);
    }

    public function test_uri()
    {
        $request = $this->resource->get->uri('nop://self/dummy')->withQuery($this->query)->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function test_eager()
    {
        $client = $this->resource->get->uri('nop://self/dummy')->withQuery($this->query);
        $expected = "nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, (string)$client);
    }


    public function testPutWithDefaultParameter()
    {
        $actual = $this->resource->post->object($this->user)->withQuery(array('id' => 1))->eager->request();
        $expected = "post user[1 default_name 99]";
        $this->assertSame($expected, $actual->body);
    }

    public function testP()
    {
        $ro = new Mock\Link;
        $actual = $this->resource->get->object($ro)->withQuery(array('id' => 1))->linkSelf('View')->eager->request();
        $expected = '<html>bear1</html>';
        $this->assertSame($expected, $actual->body);
    }

    /**
     * @expectedException BEAR\Resource\Exception\InvalidUri
     */
    public function testInvalidUri()
    {
        $query = array();
        $request = $this->resource->get->uri($this->nop)->withQuery($query)->request();
        $this->assertInstanceOf('\BEAR\Resource\Request', $request);
    }

    /**
     * @expectedException testworld\ResourceObject\Shutdown
     */
    public function testServiceError()
    {
        $query = array();
        $request = $this->resource->post->uri('app://self/blog')->eager->request();
    }

    public function testsyncHttp()
    {
        $query = array();
        $response = $this->resource
        ->get->uri('http://news.google.com/news?hl=ja&ned=us&ie=UTF-8&oe=UTF-8&output=rss')->sync->request()
        ->get->uri('http://phpspot.org/blog/index.xml')->eager->sync->request()
        ->get->uri('http://rss.excite.co.jp/rss/excite/odd')->eager->request();
        $this->assertTrue(isset($response->body[0]->channel));
    }

    public function testParameterProvidedBySignalClosure()
    {
        $signalProvider = function (
        $return,
        \ReflectionParameter $parameter,
        ReflectiveMethodInvocation $invovation,
        Definition $definition
        ) {
            $return->value = 1;
            return \Aura\Signal\Manager::STOP;
        };
        $this->resource->attachParamProvider('login_id', $signalProvider);
        $actual = $this->resource->delete->object($this->user)->withQuery([])->eager->request();
        $this->assertSame("1 deleted", $actual->body);
    }

    public function testParameterProvidedBySignalWithInvokerInterfaceObject()
    {
        $signalProvider = function (
        $return,
        \ReflectionParameter $parameter,
        ReflectiveMethodInvocation $invovation,
        Definition $definition
        ) {
            $return->value = 1;
            return \Aura\Signal\Manager::STOP;
        };

        $this->resource->attachParamProvider('Provides', new Provides);
        $this->resource->attachParamProvider('login_id', $signalProvider);
        $actual = $this->resource->delete->object($this->user)->withQuery([])->eager->request();
        $this->assertSame("1 deleted", $actual->body);
    }

    public function test_setCacheAdapter()
    {
        $cache = new CacheAdapter(new Cache);
        $this->resource->setCacheAdapter($cache);
        $instance1 = $this->resource->newInstance('nop://self/path/to/dummy');
        $instance2 = $this->resource->newInstance('nop://self/path/to/dummy');
        $this->assertSame($instance1, $instance2);
    }

    public function test_LazyReqeustResultAsString()
    {
        $additionalAnnotations = require __DIR__ . '/scripts/additionalAnnotations.php';
        $injector = new Injector(new Container(new Forge(new Config(new Annotation(new Definition)))), new EmptyModule);
        $scheme = new SchemeCollection;
        $testAdapter = new \BEAR\Resource\Adapter\Test;
        $testAdapter->setRenderer(new TestRenderer);
        $scheme->scheme('test')->host('self')->toAdapter($testAdapter);
        $this->factory = new Factory($scheme);
        $factory = new Factory($scheme);
        $this->signal = require dirname(__DIR__) . '/vendor/Aura/Signal/scripts/instance.php';
        $this->invoker = new Invoker(new Config(new Annotation(new Definition), $additonalAnnotations), new Linker, $this->signal);
        $this->resource = new Resource($factory, $this->invoker, new Request($this->invoker));
        $request = $this->resource->get->uri('test://self/path/to/example')->withQuery(['a'=>1, 'b'=>2])->request();
        $this->assertSame('{"posts":[1,2]}', (string)$request);
        $this->assertSame(['posts' => [1, 2]], $request()->body);
    }

    public function test_IndexIsDefaultIfUriEndsWithSlash()
    {
        $user = $this->resource->newInstance('app://self/user/');
        $this->assertSame($user->class, 'testworld\ResourceObject\User\Index');
    }

    public function test_IndexIsDefaultIfUriEndsWithSlashInRoot()
    {
        $user = $this->resource->newInstance('app://self/');
        $this->assertSame($user->class, 'testworld\ResourceObject\Index');
    }
}
