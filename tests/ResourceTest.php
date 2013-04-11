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
use BEAR\Resource\Builder;
use BEAR\Resource\Mock\User;
use Ray\Aop\ReflectiveMethodInvocation;
use BEAR\Resource\SignalHandler\Provides;
use Guzzle\Cache\DoctrineCacheAdapter as CacheAdapter;
use Doctrine\Common\Cache\ApcCache as Cache;
use Doctrine\Common\Annotations\AnnotationReader as Reader;
use BEAR\Resource\Mock\TestModule;

/**
 * Test class for BEAR.Resource.
 */
class ResourceTest extends \PHPUnit_Framework_TestCase
{
    protected $skeleton;

    /**
     * @var CacheAdapter
     */
    protected $cache;

    /**
     * @var Resource
     */
    private $resource;

    protected function setUp()
    {
        parent::setUp();

        // build resource client
        $scheme = new SchemeCollection;
        $injector = Injector::create();
        $injector->setModule(new TestModule);
        $scheme->scheme('app')->host('self')->toAdapter(
            new Adapter\App($injector, 'testworld', 'ResourceObject')
        );
        $scheme->scheme('page')->host('self')->toAdapter(
            new Adapter\App($injector, 'testworld', 'Page')
        );
        $scheme->scheme('nop')->host('self')->toAdapter(new Adapter\Nop);
        $scheme->scheme('test')->host('self')->toAdapter(new Adapter\Test);
        $scheme->scheme('prov')->host('self')->toAdapter(new Adapter\Prov);
        $scheme->scheme('http')->host('*')->toAdapter(new Adapter\Http);
        /** @var $resource BEAR\Resource\Resource */
        $this->resource = require dirname(__DIR__) . '/scripts/instance.php';
        $this->resource->setSchemeCollection($scheme);

        // new resource object;
        $factory = new Factory($scheme);
        $this->user = $factory->newInstance('app://self/user');
        $this->nop = $factory->newInstance('nop://self/dummy');
        $this->query = [
            'id' => 10,
            'name' => 'Ray',
            'age' => 43
        ];
        $this->cache = new CacheAdapter(new Cache);
    }

    public function test_New()
    {
        $this->assertInstanceOf('\BEAR\Resource\Resource', $this->resource);
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
        $query = [];
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
        $request = $this->resource->post->object($this->nop)->withQuery(
            $this->query
        )->poe->csrf->invalid_option_cause_exception->request();
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
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function test_linkNewString()
    {
        $request = $this->resource->get->object($this->nop)->withQuery($this->query)->linkNew('dummyLink')->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function test_linkCrawlString()
    {
        $request = $this->resource->get->object($this->nop)->withQuery($this->query)->linkCrawl('dummyLink')->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function test_linkTwo()
    {
        $request = $this->resource->get->object($this->nop)->withQuery($this->query)->linkSelf('dummyLink')->linkSelf(
            'dummyLink2'
        )->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43";
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
     * @expectedException BEAR\Resource\Exception\Uri
     */
    public function testInvalidUri()
    {
        $query = [];
        $request = $this->resource->get->uri($this->nop)->withQuery($query)->request();
        $this->assertInstanceOf('\BEAR\Resource\Request', $request);
    }

    /**
     * @expectedException testworld\ResourceObject\Shutdown
     */
    public function testServiceError()
    {
        $query = [];
        $request = $this->resource->post->uri('app://self/blog')->eager->request();
    }

    public function testsyncHttp()
    {
        $query = [];
        $response = $this->resource->get->uri(
            'http://news.google.com/news?hl=ja&ned=us&ie=UTF-8&oe=UTF-8&output=rss'
        )->sync->request()->get->uri('http://phpspot.org/blog/index.xml')->eager->sync->request()->get->uri(
            'http://rss.excite.co.jp/rss/excite/odd'
        )->eager->request();
        $this->assertSame(3, count($response->body));
    }

    public function testParameterProvidedBySignalClosure()
    {
        $this->resource->attachParamProvider('login_id', new varProvider);
        $actual = $this->resource->delete->object($this->user)->withQuery([])->eager->request();
        $this->assertSame("1 deleted", $actual->body);
    }

    public function testParameterProvidedBySignalWithInvokerInterfaceObject()
    {
        $this->resource->attachParamProvider('Provides', new Provides);
        $this->resource->attachParamProvider('login_id', new varProvider);
        $actual = $this->resource->delete->object($this->user)->withQuery([])->eager->request();
        $this->assertSame("1 deleted", $actual->body);
    }

    public function test_setCacheAdapter()
    {
        $this->resource->setCacheAdapter($this->cache);
        $instance1 = $this->resource->newInstance('nop://self/path/to/dummy');
        $instance2 = $this->resource->newInstance('nop://self/path/to/dummy');
        $this->assertSame($instance1->time, $instance2->time);
    }

    /**
     * This resource contain PDO (which can't store in cache)
     * This is expected PDOException, but nothing happened in Travis but in local.
     * So this removed temporary.
     *
     * @expectedException \PDOException
     */
    public function ignore_CacheButUnserializedInstance()
    {
        $this->resource->setCacheAdapter($this->cache);
        $instance1 = $this->resource->newInstance('app://self/cache/pdo');
        $instance2 = $this->resource->newInstance('app://self/cache/pdo');
        $this->assertNotSame($instance1->time, $instance2->time);
    }

    public function test_LazyRequestResultAsString()
    {
        $additionalAnnotations = require __DIR__ . '/scripts/additionalAnnotations.php';
        $injector = new Injector(new Container(new Forge(new Config(new Annotation(new Definition, new Reader)))), new EmptyModule);
        $scheme = new SchemeCollection;
        $testAdapter = new Adapter\Test;
        $testAdapter->setRenderer(new TestRenderer);
        $scheme->scheme('test')->host('self')->toAdapter($testAdapter);
        $this->factory = new Factory($scheme);
        $factory = new Factory($scheme);
        $this->signal = require dirname(__DIR__) . '/vendor/aura/signal/scripts/instance.php';
        $this->invoker = new Invoker(new Linker(new Reader), new ReflectiveParams(new Config(new Annotation(new Definition, new Reader)), $this->signal));
        $this->resource = new Resource($factory, $this->invoker, new Request($this->invoker));
        $request = $this->resource->get->uri('test://self/path/to/example')->withQuery(['a' => 1, 'b' => 2])->request();
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

    /**
     * @expectedException BEAR\Resource\Exception\BadRequest
     */
    public function test_badRequest_noMethod()
    {
        $request = $this->resource->uri('nop://self/dummy')->withQuery($this->query)->request();
    }

    public function tst_weavedResource()
    {
        $result = $this->resource->get->uri('app://self/weave/book')->withQuery($this->query)->eager->request();
        $result->a = 100;
        $obj = $result->___getObject();
        $this->assertSame(100, $obj->a);
    }

    public function test_docsSample01RestBucks()
    {
        ob_start();
        $response = require dirname(__DIR__) . '/docs/sample/01-rest-bucks/order.php';
        $response = ob_get_clean();
        $this->assertContains('201: Created', $response);
        $this->assertContains('Order: Success', $response);
    }

    public function test_uriWithQuery()
    {
        $response = $this->resource->get->uri('app://self/user?id=1')->eager->request();
        $expected = 'Aramis';
        $this->assertSame($expected, $response->body['name']);
    }

    public function test_verbOptions()
    {
        $response = $this->resource->options->uri('app://self/user')->eager->request();
        $expected = ['get', 'post', 'put', 'delete'];
        $this->assertSame($expected, $response->headers['allow']);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\Uri
     */
    public function test_invalidUri()
    {
        $response = $this->resource->get->uri('invalid')->eager->request();
    }

    public function test_usingUri()
    {
        $uri = new Uri('app://self/user', ['id' => 1]);
        $response = $this->resource->get->uri($uri)->eager->request();
        $expected = 'Aramis';
        $this->assertSame($expected, $response->body['name']);
    }

    public function test_headRequest()
    {
        $uri = new Uri('app://self/user', ['id' => 1]);
        $response = $this->resource->head->uri($uri)->eager->request();
        $this->assertSame('', $response->body);
        $this->assertSame('123', $response->headers['x-header-test']);
    }

}

class varProvider implements SignalHandler\HandleInterface
{
    public function __invoke(
        $return,
        \ReflectionParameter $parameter,
        ReflectiveMethodInvocation $invocation,
        Definition $definition
    ) {
        $return->value = 1;

        return \Aura\Signal\Manager::STOP;
    }
}
