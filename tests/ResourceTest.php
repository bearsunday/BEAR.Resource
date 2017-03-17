<?php
/**
 * This file is part of the BEAR.Sunday package.
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
use Ray\Di\EmptyModule;
use Ray\Di\Injector;

class ResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceInterface
     */
    private $resource;

    protected function setUp()
    {
        parent::setUp();
        $this->resource = (
        new Injector(new FakeSchemeModule(new ResourceModule('FakeVendor\Sandbox')), $_ENV['TMP_DIR']))->getInstance(ResourceInterface::class);
    }

    public function testManualConstruction()
    {
        $injector = new Injector(new EmptyModule, $_ENV['TMP_DIR']);
        $reader = new AnnotationReader;
        $scheme = (new SchemeCollection)
            ->scheme('app')->host('self')->toAdapter(new AppAdapter($injector, 'FakeVendor\Sandbox', 'Resource\App'))
            ->scheme('page')->host('self')->toAdapter(new AppAdapter($injector, 'FakeVendor\Sandbox', 'Resource\Page'))
            ->scheme('nop')->host('self')->toAdapter(new FakeNop);
        $invoker = new Invoker(new NamedParameter(new ArrayCache, new VoidParamHandler));
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
        $resourceObject = new Index;
        $resourceObject->uri = new Uri('page://self/index');
        $instance = $this->resource->get->object($resourceObject)->eager->request();
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
        $request = $this->resource->get->uri('app://self/author')->linkSelf('blog')->request();
        /* @var $request Request */
        $this->assertSame('blog', $request->links[0]->key);
        $this->assertSame(LinkType::SELF_LINK, $request->links[0]->type);
    }

    public function testLinkNew()
    {
        $request = $this->resource->get->uri('app://self/author')->linkNew('blog')->request();
        /* @var $request Request */
        $this->assertSame('blog', $request->links[0]->key);
        $this->assertSame(LinkType::NEW_LINK, $request->links[0]->type);
    }

    public function testLinkCrawl()
    {
        $request = $this->resource->get->uri('app://self/author')->linkCrawl('blog')->request();
        /* @var $request Request */
        $this->assertSame('blog', $request->links[0]->key);
        $this->assertSame(LinkType::CRAWL_LINK, $request->links[0]->type);
    }

    public function testHal()
    {
        $resource = (new Injector(new HalModule(new ResourceModule('FakeVendor\Sandbox'))))->getInstance(
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
}
