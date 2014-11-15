<?php

namespace BEAR\Resource;

use Doctrine\Common\Annotations\AnnotationReader;
use FakeVendor\Sandbox\Resource\App\Blog;
use FakeVendor\Sandbox\Resource\Page\Index;
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
        $injector = new Injector;
        $scheme = (new SchemeCollection)
        ->scheme('app')->host('self')->toAdapter(new AppAdapter($injector, 'FakeVendor\Sandbox', 'Resource\App'))
        ->scheme('page')->host('self')->toAdapter(new AppAdapter($injector, 'FakeVendor\Sandbox', 'Resource\Page'))
        ->scheme('nop')->host('self')->toAdapter(new FakeNop);
        $reader = new AnnotationReader;
        $invoker = new Invoker(new NamedParameter);
        $factory = new Factory($scheme);
        $this->resource = new Resource(
            $factory,
            $invoker,
            new Anchor($reader),
            new Linker($reader, $invoker, $factory)
        );
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
        $instance = $this->resource->get->uri('page://self/index')->withQuery(['id' => 1])->addQuery(['id' => 2])->eager->request();
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

    public function testLinkSelf()
    {
        $request = $this->resource
            ->get
            ->uri('app://self/author')
            ->linkSelf('blog')
            ->request();
        /** @var $request Request */
        $this->assertSame('blog', $request->links[0]->key);
        $this->assertSame(LinkType::SELF_LINK, $request->links[0]->type);
    }

    public function testLinkNew()
    {
        $request = $this->resource
            ->get
            ->uri('app://self/author')
            ->linkNew('blog')
            ->request();
        /** @var $request Request */
        $this->assertSame('blog', $request->links[0]->key);
        $this->assertSame(LinkType::NEW_LINK, $request->links[0]->type);
    }

    public function testLinkCrawl()
    {
        $request = $this->resource
            ->get
            ->uri('app://self/author')
            ->linkCrawl('blog')
            ->request();
        /** @var $request Request */
        $this->assertSame('blog', $request->links[0]->key);
        $this->assertSame(LinkType::CRAWL_LINK, $request->links[0]->type);
    }
}
