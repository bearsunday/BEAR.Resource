<?php

namespace BEAR\Resource;

use Aura\Signal\HandlerFactory;
use Aura\Signal\Manager;
use Aura\Signal\ResultCollection;
use Aura\Signal\ResultFactory;
use BEAR\Resource\Mock\TestModule;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\FilesystemCache;
use Guzzle\Cache\DoctrineCacheAdapter as CacheAdapter;
use Guzzle\Parser\UriTemplate\UriTemplate;
use Ray\Di\Definition;
use Ray\Di\Injector;
use BEAR\Resource\Renderer\TestRenderer;
use Sandbox\Resource\App\Link;

class varProvider implements ParamProviderInterface
{
    public function __invoke(Param $param)
    {
        return $param->inject(1);
    }
}

/**
 * Test class for BEAR.Resource.
 */
class ResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CacheAdapter
     */
    protected $cache;

    /**
     * @var \BEAR\Resource\Resource
     */
    private $resource;

    /**
     * @var \Sandbox\Resource\App\User
     */
    private $user;

    protected function setUp()
    {
        parent::setUp();

        // build resource client
        $scheme = new SchemeCollection;
        $injector = Injector::create([new TestModule]);
        $scheme->scheme('app')->host('self')->toAdapter(
            new Adapter\App($injector, 'Sandbox', 'Resource\App')
        );
        $scheme->scheme('page')->host('self')->toAdapter(
            new Adapter\App($injector, 'Sandbox', 'Resource\Page')
        );
        $scheme->scheme('nop')->host('self')->toAdapter(new Adapter\Nop);
        $scheme->scheme('test')->host('self')->toAdapter(new Adapter\Test);
        $scheme->scheme('prov')->host('self')->toAdapter(new Adapter\Prov);
        $scheme->scheme('http')->host('*')->toAdapter(new Adapter\Http);
        $resource = require dirname(dirname(dirname(__DIR__))) . '/scripts/instance.php';
        /** @var $resource \BEAR\Resource\Resource */
        $this->resource = $resource;
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
        $this->cache = new CacheAdapter(new FilesystemCache($_ENV['BEAR_TMP']));
    }

    public function testNew()
    {
        $this->assertInstanceOf('\BEAR\Resource\Resource', $this->resource);
    }

    public function testNewInstanceNop()
    {
        $instance = $this->resource->newInstance('nop://self/path/to/dummy');
        $this->assertInstanceOf('\BEAR\Resource\Adapter\Nop', $instance);
    }

    public function testNewInstanceAppWithProvider()
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

    public function testGet()
    {
        $request = $this->resource->get->object($this->nop)->withQuery($this->query)->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function testPost()
    {
        $request = $this->resource->post->object($this->nop)->withQuery($this->query)->request();
        $expected = "post nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function testPut()
    {
        $request = $this->resource->put->object($this->nop)->withQuery($this->query)->request();
        $expected = "put nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function testDelete()
    {
        $request = $this->resource->delete->object($this->nop)->withQuery($this->query)->request();
        $expected = "delete nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function testLinkSelfString()
    {
        $request = $this->resource->get->object($this->nop)->withQuery($this->query)->linkSelf('dummyLink')->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function testLinkNewString()
    {
        $request = $this->resource->get->object($this->nop)->withQuery($this->query)->linkNew('dummyLink')->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function testLinkCrawlString()
    {
        $request = $this->resource->get->object($this->nop)->withQuery($this->query)->linkCrawl('dummyLink')->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function testLinkTwo()
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

    public function testUri()
    {
        $request = $this->resource->get->uri('nop://self/dummy')->withQuery($this->query)->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function testEager()
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

    /**
     * @expectedException \BEAR\Resource\Exception\Uri
     */
    public function testInvalidUriThrowException()
    {
        $query = [];
        $request = $this->resource->get->uri($this->nop)->withQuery($query)->request();
        $this->assertInstanceOf('\BEAR\Resource\Request', $request);
    }

    public function testSyncHttp()
    {
        $response = $this
            ->resource
            ->get
            ->uri('http://news.google.com/news?hl=ja&ned=us&ie=UTF-8&oe=UTF-8&output=rss')
            ->sync
            ->request()
            ->get
            ->uri('http://phpspot.org/blog/index.xml')
            ->eager
            ->sync
            ->request()
            ->get
            ->uri('http://rss.excite.co.jp/rss/excite/odd')
            ->eager
            ->request();
        $this->assertSame(3, count($response->body));
    }

    public function testAttachParamProvider()
    {
        $this->resource->attachParamProvider('delete_id', new varProvider);
        $actual = $this->resource->delete->object($this->user)->withQuery([])->eager->request();
        $this->assertSame("1 deleted", $actual->body);
    }

    public function testSetCacheAdapter()
    {
        $this->resource->setCacheAdapter($this->cache);
        $instance1 = $this->resource->newInstance('nop://self/path/to/dummy');
        $instance2 = $this->resource->newInstance('nop://self/path/to/dummy');
        $this->assertSame($instance1->time, $instance2->time);
    }

    public function testLazyRequestResultAsString()
    {
        $scheme = new SchemeCollection;
        $testAdapter = new Adapter\Test;
        $testAdapter->setRenderer(new TestRenderer);
        $scheme->scheme('test')->host('self')->toAdapter($testAdapter);
        $this->factory = new Factory($scheme);
        $this->resource =  require dirname(dirname(dirname(__DIR__))) . '/scripts/instance.php';

        $invoker = new Invoker(new Linker(new AnnotationReader), new NamedParams(new SignalParam(new Manager(new HandlerFactory, new ResultFactory, new ResultCollection), new Param)), new Logger);
        $resource = new Resource(new Factory($scheme), $invoker, new Request($invoker), new Anchor(new UriTemplate, new AnnotationReader, new Request($invoker)));
        $request = $resource->get->uri('test://self/path/to/example')->withQuery(['a' => 1, 'b' => 2])->request();
        $this->assertSame('{"posts":[1,2]}', (string)$request);
        $this->assertSame(['posts' => [1, 2]], $request()->body);
    }

    public function testIndexIsDefaultIfUriEndsWithSlash()
    {
        $user = $this->resource->newInstance('app://self/user/');
        $this->assertSame($user->class, 'Sandbox\Resource\App\User\Index');
    }

    public function testIndexIsDefaultIfUriEndsWithSlashInRoot()
    {
        $user = $this->resource->newInstance('app://self/');
        $this->assertSame($user->class, 'Sandbox\Resource\App\Index');
    }

    /**
     * @expectedException \BEAR\Resource\Exception\BadRequest
     */
    public function testBadRequestNoMethod()
    {
        $this->resource->uri('nop://self/dummy')->withQuery($this->query)->request();
    }

    public function testWeavedResource()
    {
        $result = $this->resource->get->uri('app://self/weave/book')->withQuery($this->query)->eager->request();
        $result->a = 100;
        $this->assertSame(100, $result->a);
    }

    public function testDocsSample00min()
    {
        ob_start();
        require dirname(dirname(dirname(__DIR__))) . '/docs/sample/00.min/main.php';
        $response = ob_get_clean();
        $this->assertContains('[name] => Aramis', $response);
    }

//    public function testDocsSample01basic()
//    {
//        ob_start();
//        require dirname(dirname(dirname(__DIR__))) . '/docs/sample/01.basic/main.php ';
//        $response = ob_get_clean();
//        $this->assertContains('[name] => Aramis', $response);
//    }
//
//    public function testDocsSample02basic()
//    {
//        ob_start();
//        require dirname(dirname(dirname(__DIR__))) . '/docs/sample/01.basic/main.php ';
//        $response = ob_get_clean();
//        $this->assertContains('[name] => Aramis', $response);
//    }

    public function testDocsSampleRestBucks()
    {
        ob_start();
        require dirname(dirname(dirname(__DIR__))) . '/docs/sample/Restbucks/main.php';
        $response = ob_get_clean();
        $this->assertContains('201: Created', $response);
        $this->assertContains('Order: Success', $response);
    }

    public function testUriWithQuery()
    {
        $response = $this->resource->get->uri('app://self/user?id=1')->eager->request();
        $expected = 'Aramis';
        $this->assertSame($expected, $response->body['name']);
    }

    public function testVerbOptions()
    {
        $response = $this->resource->options->uri('app://self/user')->eager->request();
        $expected = ['get', 'post', 'put', 'delete'];
        $this->assertSame($expected, $response->headers['allow']);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\Uri
     */
    public function testInvalidUri()
    {
        $this->resource->get->uri('invalid')->eager->request();
    }

    public function testUsingUri()
    {
        $uri = new Uri('app://self/user', ['id' => 1]);
        $response = $this->resource->get->uri($uri)->eager->request();
        $expected = 'Aramis';
        $this->assertSame($expected, $response->body['name']);
    }

    public function testHeadRequest()
    {
        $uri = new Uri('app://self/user', ['id' => 1]);
        $response = $this->resource->head->uri($uri)->eager->request();
        $this->assertSame('', $response->body);
        $this->assertSame('123', $response->headers['x-header-test']);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\BadRequest
     */
    public function testInvalidOption()
    {
        $this->resource->invalid_xxx;
    }
}
