<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\MethodNotAllowedException;
use BEAR\Resource\Module\HalModule;
use BEAR\Resource\Module\ResourceModule;
use Doctrine\Common\Annotations\AnnotationReader;
use FakeVendor\Sandbox\Resource\App\Blog;
use FakeVendor\Sandbox\Resource\App\Href\Hasembed;
use FakeVendor\Sandbox\Resource\App\Href\Origin;
use FakeVendor\Sandbox\Resource\App\Href\Target;
use FakeVendor\Sandbox\Resource\Page\Index;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;
use Ray\Di\NullModule;

class ResourceTest extends TestCase
{
    /**
     * @var ResourceInterface
     */
    private $resource;

    protected function setUp() : void
    {
        parent::setUp();
        $injector = new Injector(new FakeSchemeModule(new ResourceModule('FakeVendor\Sandbox')), __DIR__ . '/tmp');
        $this->resource = $injector->getInstance(ResourceInterface::class);
    }

    public function testManualConstruction() : void
    {
        $injector = new Injector(new NullModule, __DIR__ . '/tmp');
        $reader = new AnnotationReader;
        $scheme = (new SchemeCollection)
            ->scheme('app')->host('self')->toAdapter(new AppAdapter($injector, 'FakeVendor\Sandbox'))
            ->scheme('page')->host('self')->toAdapter(new AppAdapter($injector, 'FakeVendor\Sandbox'))
            ->scheme('nop')->host('self')->toAdapter(new FakeNop);
        $invoker = (new InvokerFactory)();
        $factory = new Factory($scheme, new UriFactory);
        $uri = new UriFactory('app://self');
        $resource = new Resource($factory, $invoker, new Anchor($reader), new Linker($reader, $invoker, $factory), $uri);
        $this->assertInstanceOf(ResourceInterface::class, $resource);
    }

    public function testNewInstance() : void
    {
        $instance = $this->resource->newInstance('page://self/index');
        $this->assertInstanceOf(Index::class, $instance);
    }

    public function testLazyRequest() : void
    {
        $instance = $this->resource->get->uri('page://self/index')->request();
        $this->assertInstanceOf(Request::class, $instance);
    }

    public function testEagerRequest() : void
    {
        $instance = $this->resource->get->uri('page://self/index')->eager->request();
        $this->assertInstanceOf(Index::class, $instance);
    }

    public function testWithQueryRequest() : void
    {
        $instance = $this->resource->get->uri('page://self/index')->withQuery(['id' => 1])->eager->request();
        $this->assertSame(1, $instance->body);
    }

    public function testWithAddRequestOverrideQuery() : void
    {
        $instance = $this->resource->get->uri('page://self/index')->withQuery(['id' => 1])->addQuery(
            ['id' => 2]
        )->eager->request();
        $this->assertSame(2, $instance->body);
    }

    public function testObject() : void
    {
        $ro = new Index;
        $ro->uri = new Uri('page://self/index');
        $instance = $this->resource->get->object($ro)->eager->request();
        $this->assertInstanceOf(Index::class, $instance);
    }

    public function testHref() : void
    {
        $this->resource->get->uri('app://self/author')->withQuery(['id' => 1])->eager->request();
        $blog = $this->resource->href('blog');
        $this->assertInstanceOf(Blog::class, $blog);
    }

    public function testHrefInResourceObject() : void
    {
        $origin = $this->resource->get->uri('app://self/href/origin')->withQuery(['id' => 1])->eager->request();
        $this->assertInstanceOf(Origin::class, $origin);
        $this->assertInstanceOf(Target::class, $origin['next']);
        $next = $origin['next'];
        $this->assertSame($next['id'], 1);
    }

    public function testHrefInResourceObjectHasEmbed() : void
    {
        $origin = $this->resource->get->uri('app://self/href/hasembed')->withQuery(['id' => 1])->eager->request();
        $this->assertInstanceOf(Hasembed::class, $origin);
        $this->assertInstanceOf(Target::class, $origin['next']);
        $next = $origin['next'];
        $this->assertSame($next['id'], 1);
    }

    public function testLinkSelf() : void
    {
        $request = $this->resource->get->uri('app://self/author')->withQuery(['id' => 1])->linkSelf('blog')->request();
        /* @var $request Request */
        $this->assertSame('blog', $request->links[0]->key);
        $this->assertSame(LinkType::SELF_LINK, $request->links[0]->type);
        $ro = $request();
        $v = var_export($ro->body, true);
        $this->assertSame(200, $ro->code);
        $this->assertSame(['id' => 12, 'name' => 'Aramis blog'], $ro->body);
    }

    public function testLinkNew() : void
    {
        $request = $this->resource->get->uri('app://self/author')->withQuery(['id' => 1])->linkNew('blog')->request();
        /* @var $request Request */
        $this->assertSame('blog', $request->links[0]->key);
        $this->assertSame(LinkType::NEW_LINK, $request->links[0]->type);
        $ro = $request();
        $this->assertSame(200, $ro->code);
        $this->assertSame(
            [
                'name' => 'Aramis',
                'age' => 16,
                'blog_id' => 12,
                'blog' => ['id' => 12, 'name' => 'Aramis blog']
            ],
            $ro->body
        );
    }

    public function testLinkCrawl() : array
    {
        $request = $this->resource->get->uri('app://self/blog')->withQuery(['id' => 11])->linkCrawl('tree')->request();
        /* @var $request Request */
        $this->assertSame('tree', $request->links[0]->key);
        $this->assertSame(LinkType::CRAWL_LINK, $request->links[0]->type);
        $ro = $request();
        $this->assertSame(200, $ro->code);
        $expected = [
            'id' => 11,
            'name' => 'Athos blog',
            'post' => [
                'id' => '1',
                'author_id' => '1',
                'body' => 'Anna post #1',
                'meta' => [
                    0 => [
                        'id' => '1',
                        'post_id' => '1',
                        'data' => 'meta 1'
                    ],
                ],
                'tag' => [
                    0 => [
                        'id' => '1',
                        'post_id' => '1',
                        'tag_id' => '1',
                        'tag_name' => [
                            0 => [
                                'id' => '1',
                                'name' => 'zim'
                            ],
                        ],
                        'tag_type' => [
                            0 => 'type1'
                        ],
                    ],
                    1 => [
                        'id' => '2',
                        'post_id' => '1',
                        'tag_id' => '2',
                        'tag_name' => [
                            0 => [
                                'id' => '2',
                                'name' => 'dib'
                            ],
                        ],
                        'tag_type' => [
                            0 => 'type1'
                        ],
                    ],
                ]
            ],
        ];
        $this->assertSame($expected, $ro->body);

        return $expected;
    }

    public function testHal() : void
    {
        $resource = (new Injector(new HalModule(new ResourceModule('FakeVendor\Sandbox')), __DIR__ . '/tmp'))->getInstance(
            'BEAR\Resource\ResourceInterface'
        );
        $user = $resource->get->uri('app://self/author')->withQuery(['id' => 1])->eager->request();
        $expected = '{
    "name": "Aramis",
    "age": 16,
    "blog_id": 12,
    "_links": {
        "self": {
            "href": "/author?id=1"
        },
        "blog": {
            "href": "app://self/blog?id=12"
        }
    }
}
';
        $this->assertSame($expected, (string) $user);
    }

    public function testConstructorHasAnotherResourceRequest() : void
    {
        $body = $this->resource->post->uri('app://self/holder')->eager->request()->body;
        $this->assertTrue($body);
    }

    public function testAssistedParameter() : ResourceInterface
    {
        $injector = new Injector(new FakeAssistedModule(new FakeSchemeModule(new ResourceModule('FakeVendor\Sandbox'))), __DIR__ . '/tmp');
        $this->resource = $injector->getInstance(ResourceInterface::class);
        $ro = $this->resource->get->uri('page://self/assist')->eager->request();
        /* @var $ro \BEAR\Resource\ResourceObject */
        $this->assertSame('login_id:assisted01', $ro->body);

        return $this->resource;
    }

    /**
     * @depends testAssistedParameter
     */
    public function testPreventAssistedParameterOverride(ResourceInterface $resource) : void
    {
        $ro = $resource->get->uri('page://self/assist')->withQuery(['login_id' => '_WILL_BE_IGNORED_'])->eager->request();
        /* @var $ro \BEAR\Resource\ResourceObject */
        $this->assertSame('login_id:assisted01', $ro->body);
    }

    public function testGet() : void
    {
        $ro = $this->resource->get('page://self/index', ['id' => 1]);
        $this->assertSame(1, $ro->body);
    }

    public function testPost() : void
    {
        $ro = $this->resource->post('page://self/index', ['name' => 'bear']);
        $this->assertSame('post bear', $ro->body);
    }

    public function testPut() : void
    {
        $ro = $this->resource->put('page://self/index', ['name' => 'bear']);
        $this->assertSame('put bear', $ro->body);
    }

    public function testPatch() : void
    {
        $ro = $this->resource->patch('page://self/index', ['name' => 'bear']);
        $this->assertSame('patch bear', $ro->body);
    }

    public function testDelete() : void
    {
        $ro = $this->resource->delete('page://self/index', ['name' => 'bear']);
        $this->assertSame('delete bear', $ro->body);
    }

    public function testHead() : void
    {
        $ro = $this->resource->head('page://self/index', ['name' => 'bear']);
        $this->assertSame('1', $ro->headers['X-BEAR']);
        $this->assertNull($ro->body);
    }

    public function testHeadNotAllowed() : void
    {
        $this->expectException(MethodNotAllowedException::class);
        $this->resource->head('page://self/hello-world');
    }

    public function testMultipleRequest() : void
    {
        $view = (string) $this->resource->get('/fake-loop');
        $expected = '{
    "1": {
        "num": "1"
    },
    "2": {
        "num": "2"
    },
    "3": {
        "num": "3"
    },
    "4": {
        "num": "4"
    },
    "5": {
        "num": "5"
    }
}
';
        $this->assertSame($expected, $view);
    }
}
