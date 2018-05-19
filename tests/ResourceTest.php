<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Module\HalModule;
use BEAR\Resource\Module\ResourceModule;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use FakeVendor\Sandbox\Resource\App\Blog;
use FakeVendor\Sandbox\Resource\App\Href\Hasembed;
use FakeVendor\Sandbox\Resource\App\Href\Origin;
use FakeVendor\Sandbox\Resource\App\Href\Target;
use FakeVendor\Sandbox\Resource\Page\Index;
use PHPUnit\Framework\TestCase;
use Ray\Di\EmptyModule;
use Ray\Di\Injector;

class ResourceTest extends TestCase
{
    /**
     * @var ResourceInterface
     */
    private $resource;

    protected function setUp()
    {
        parent::setUp();
        $injector = new Injector(new FakeSchemeModule(new ResourceModule('FakeVendor\Sandbox')), $_ENV['TMP_DIR']);
        $this->resource = $injector->getInstance(ResourceInterface::class);
    }

    public function testManualConstruction()
    {
        $injector = new Injector(new EmptyModule, $_ENV['TMP_DIR']);
        $reader = new AnnotationReader;
        $scheme = (new SchemeCollection)
            ->scheme('app')->host('self')->toAdapter(new AppAdapter($injector, 'FakeVendor\Sandbox'))
            ->scheme('page')->host('self')->toAdapter(new AppAdapter($injector, 'FakeVendor\Sandbox'))
            ->scheme('nop')->host('self')->toAdapter(new FakeNop);
        $invoker = new Invoker(new NamedParameter(new NamedParamMetas(new ArrayCache, new AnnotationReader), new Injector), new OptionsRenderer(new OptionsMethods($reader)));
        $factory = new Factory($scheme);
        $resource = new Resource($factory, $invoker, new Anchor($reader), new Linker($reader, $invoker, $factory));
        $this->assertInstanceOf(ResourceInterface::class, $resource);
    }

    public function testNewInstance()
    {
        $instance = $this->resource->newInstance('page://self/index');
        $this->assertInstanceOf(Index::class, $instance);
    }

    public function testLazyRequest()
    {
        $instance = $this->resource->get->uri('page://self/index')->request();
        $this->assertInstanceOf(Request::class, $instance);
    }

    public function testEagerRequest()
    {
        $instance = $this->resource->get->uri('page://self/index')->eager->request();
        $this->assertInstanceOf(Index::class, $instance);
    }

    public function testWithQueryRequest()
    {
        $instance = $this->resource->get->uri('page://self/index')->withQuery(['id' => 1])->eager->request();
        $this->assertSame(1, $instance->body);
    }

    public function testWithAddRequestOverrideQuery()
    {
        $instance = $this->resource->get->uri('page://self/index')->withQuery(['id' => 1])->addQuery(
            ['id' => 2]
        )->eager->request();
        $this->assertSame(2, $instance->body);
    }

    public function testObject()
    {
        $ro = new Index;
        $ro->uri = new Uri('page://self/index');
        $instance = $this->resource->get->object($ro)->eager->request();
        $this->assertInstanceOf(Index::class, $instance);
    }

    public function testHref()
    {
        $this->resource->get->uri('app://self/author')->withQuery(['id' => 1])->eager->request();
        $blog = $this->resource->href('blog');
        $this->assertInstanceOf(Blog::class, $blog);
    }

    public function testHrefInResourceObject()
    {
        $origin = $this->resource->get->uri('app://self/href/origin')->withQuery(['id' => 1])->eager->request();
        $this->assertInstanceOf(Origin::class, $origin);
        $this->assertInstanceOf(Target::class, $origin['next']);
        $next = $origin['next'];
        $this->assertSame($next['id'], 1);
    }

    public function testHrefInResourceObjectHasEmbed()
    {
        $origin = $this->resource->get->uri('app://self/href/hasembed')->withQuery(['id' => 1])->eager->request();
        $this->assertInstanceOf(Hasembed::class, $origin);
        $this->assertInstanceOf(Target::class, $origin['next']);
        $next = $origin['next'];
        $this->assertSame($next['id'], 1);
    }

    public function testLinkSelf()
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

    public function testLinkNew()
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

    public function testLinkCrawl()
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
    }

    public function testHal()
    {
        $resource = (new Injector(new HalModule(new ResourceModule('FakeVendor\Sandbox')), $_ENV['TMP_DIR']))->getInstance(
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

    public function testConstructorHasAnotherResourceRequest()
    {
        $body = $this->resource->post->uri('app://self/holder')->eager->request()->body;
        $this->assertTrue($body);
    }

    public function testAssistedParameter()
    {
        $injector = new Injector(new FakeAssistedModule(new FakeSchemeModule(new ResourceModule('FakeVendor\Sandbox'))), $_ENV['TMP_DIR']);
        $this->resource = $injector->getInstance(ResourceInterface::class);
        $ro = $this->resource->get->uri('page://self/assist')->eager->request();
        /* @var $ro \BEAR\Resource\ResourceObject */
        $this->assertSame('login_id:assisted01', $ro->body);

        return $this->resource;
    }

    /**
     * @depends testAssistedParameter
     */
    public function testPreventAssistedParameterOverride(ResourceInterface $resource)
    {
        $ro = $resource->get->uri('page://self/assist')->withQuery(['login_id' => '_WILL_BE_IGNORED_'])->eager->request();
        /* @var $ro \BEAR\Resource\ResourceObject */
        $this->assertSame('login_id:assisted01', $ro->body);
    }
}
