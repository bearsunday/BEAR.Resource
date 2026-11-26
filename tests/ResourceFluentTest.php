<?php

declare(strict_types=1);

namespace BEAR\Resource;

use FakeVendor\Sandbox\Resource\App\Blog;
use FakeVendor\Sandbox\Resource\Page\Index;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;
use Ray\Di\NullModule;
use Ray\Di\ProviderInterface;

use function assert;

/**
 * Tests for Resource class (fluent interface implementation)
 *
 * ResourceClient delegates to Resource via __get() for fluent interface support.
 * These tests exercise Resource directly to ensure full coverage.
 */
class ResourceFluentTest extends TestCase
{
    private Resource $resource;

    protected function setUp(): void
    {
        $injector = new Injector(new NullModule(), __DIR__ . '/tmp');
        $scheme = (new SchemeCollection())
            ->scheme('app')->host('self')->toAdapter(new AppAdapter($injector, 'FakeVendor\Sandbox'))
            ->scheme('page')->host('self')->toAdapter(new AppAdapter($injector, 'FakeVendor\Sandbox'))
            ->scheme('nop')->host('self')->toAdapter(new FakeNop());
        $invoker = (new InvokerFactory())();
        $factory = new Factory($scheme, new UriFactory());
        /** @var ProviderInterface<LinkCrawlerInterface> $linkCrawlerProvider */
        $linkCrawlerProvider = new class ($invoker, $factory) implements ProviderInterface {
            public function __construct(
                private readonly InvokerInterface $invoker,
                private readonly FactoryInterface $factory,
            ) {
            }

            public function get(): LinkCrawlerInterface
            {
                return new LinkCrawler($this->invoker, $this->factory);
            }
        };
        $linker = new Linker($invoker, $factory, $linkCrawlerProvider);
        $uri = new UriFactory('app://self');

        $this->resource = new Resource($factory, $invoker, new Anchor(), $linker, $uri);
    }

    public function testGetMethod(): void
    {
        $self = $this->resource->__get('get');
        $this->assertInstanceOf(Resource::class, $self);
    }

    public function testNewInstance(): void
    {
        $ro = $this->resource->newInstance('page://self/index');
        $this->assertInstanceOf(Index::class, $ro);
    }

    public function testObject(): void
    {
        $ro = new Index();
        $ro->uri = new Uri('page://self/index');
        $request = $this->resource->object($ro);
        $this->assertInstanceOf(RequestInterface::class, $request);
    }

    public function testUri(): void
    {
        $this->resource->__get('get');
        $request = $this->resource->uri('page://self/index');
        $this->assertInstanceOf(RequestInterface::class, $request);
    }

    public function testNewRequest(): void
    {
        $request = $this->resource->newRequest(Method::POST, 'app://self/blog', ['name' => 'test']);
        $this->assertInstanceOf(RequestInterface::class, $request);
        assert($request instanceof Request);
        $this->assertSame(Method::POST, $request->method);
    }

    public function testCrawl(): void
    {
        $ro = $this->resource->crawl('app://self/blog', 'tree', ['id' => 11]);
        $this->assertSame(200, $ro->code);
        $this->assertArrayHasKey('post', $ro->body);
    }

    public function testHrefWithResourceObject(): void
    {
        $author = $this->resource->get('app://self/author', ['id' => 1]);
        $blog = $this->resource->href('blog', [], $author);
        $this->assertInstanceOf(Blog::class, $blog);
    }

    public function testHrefWithInternalState(): void
    {
        // Use fluent interface to set up internal $this->request state
        $this->resource->__get('get');
        $request = $this->resource->uri('app://self/author?id=1');
        assert($request instanceof Request);
        $request->eager->request();

        // href without $ro uses internal $this->request
        $blog = $this->resource->href('blog');
        $this->assertInstanceOf(Blog::class, $blog);
    }

    public function testGet(): void
    {
        $ro = $this->resource->get('page://self/index', ['id' => 1]);
        $this->assertSame(1, $ro->body);
    }

    public function testPost(): void
    {
        $ro = $this->resource->post('page://self/index', ['name' => 'bear']);
        $this->assertSame('post bear', $ro->body);
    }

    public function testPut(): void
    {
        $ro = $this->resource->put('page://self/index', ['name' => 'bear']);
        $this->assertSame('put bear', $ro->body);
    }

    public function testPatch(): void
    {
        $ro = $this->resource->patch('page://self/index', ['name' => 'bear']);
        $this->assertSame('patch bear', $ro->body);
    }

    public function testDelete(): void
    {
        $ro = $this->resource->delete('page://self/index', ['name' => 'bear']);
        $this->assertSame('delete bear', $ro->body);
    }

    public function testOptions(): void
    {
        $ro = $this->resource->options('page://self/index');
        $this->assertInstanceOf(ResourceObject::class, $ro);
    }

    public function testHead(): void
    {
        $ro = $this->resource->head('page://self/index', ['name' => 'bear']);
        $this->assertSame('1', $ro->headers['X-BEAR']);
    }
}
