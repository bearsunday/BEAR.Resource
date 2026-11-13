<?php

declare(strict_types=1);

namespace BEAR\Resource\DataLoader;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\AppAdapter;
use BEAR\Resource\Exception\UriException;
use BEAR\Resource\Factory;
use BEAR\Resource\FactoryInterface;
use BEAR\Resource\FakeRo;
use BEAR\Resource\InvokerFactory;
use BEAR\Resource\InvokerInterface;
use BEAR\Resource\LinkCrawler;
use BEAR\Resource\LinkCrawlerInterface;
use BEAR\Resource\Linker;
use BEAR\Resource\LinkType;
use BEAR\Resource\Method;
use BEAR\Resource\Request;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\SchemeCollection;
use BEAR\Resource\UriFactory;
use FakeVendor\Sandbox\DataLoader\LikeDataLoader;
use FakeVendor\Sandbox\Resource\App\Batch\Article;
use FakeVendor\Sandbox\Resource\App\Batch\LinkedComment;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;
use Ray\Di\ProviderInterface;
use ReflectionClass;

class LinkerDataLoaderTest extends TestCase
{
    private Linker $linker;
    private Request $request;

    protected function setUp(): void
    {
        LikeDataLoader::reset();

        $invoker = (new InvokerFactory())();
        $injector = new Injector();
        $schemeCollection = (new SchemeCollection())
            ->scheme('app')
            ->host('self')
            ->toAdapter(new AppAdapter($injector, 'FakeVendor\Sandbox'));

        $factory = new Factory($schemeCollection, new UriFactory());
        $dataLoader = new DataLoader($injector);
        /** @var ProviderInterface<LinkCrawlerInterface> $linkCrawlerProvider */
        $linkCrawlerProvider = new class ($invoker, $factory, $dataLoader) implements ProviderInterface {
            public function __construct(
                private readonly InvokerInterface $invoker,
                private readonly FactoryInterface $factory,
                private readonly DataLoader|null $dataLoader,
            ) {
            }

            public function get(): LinkCrawlerInterface
            {
                return new LinkCrawler($this->invoker, $this->factory, $this->dataLoader);
            }
        };

        $this->linker = new Linker(
            $invoker,
            $factory,
            $linkCrawlerProvider,
        );
    }

    public function testDataLoaderCrawl(): void
    {
        $invoker = (new InvokerFactory())();
        $this->request = new Request(
            $invoker,
            (new FakeRo())(new Article()),
            Method::GET,
            ['id' => 1],
            [new LinkType('comment-tree', LinkType::CRAWL_LINK)],
        );

        $result = $this->linker->invoke($this->request);

        // Verify structure
        $this->assertSame(1, $result->body['id']);
        $this->assertSame('Article 1', $result->body['title']);
        $this->assertArrayHasKey('comment', $result->body);

        // Verify comments have likes
        $comments = $result->body['comment'];
        $this->assertCount(3, $comments);

        // Comment 10 should have 2 likes
        $this->assertSame(10, $comments[0]['id']);
        $this->assertArrayHasKey('like', $comments[0]);
        $this->assertCount(2, $comments[0]['like']);
        $this->assertSame(100, $comments[0]['like'][0]['id']);
        $this->assertSame('user1', $comments[0]['like'][0]['user_id']);

        // Comment 11 should have 1 like
        $this->assertSame(11, $comments[1]['id']);
        $this->assertCount(1, $comments[1]['like']);

        // Comment 12 should have 3 likes
        $this->assertSame(12, $comments[2]['id']);
        $this->assertCount(3, $comments[2]['like']);

        // DataLoader should be called only once (not 3 times for 3 comments)
        $this->assertSame(1, LikeDataLoader::$callCount);
    }

    public function testALinkInTheFragmentOfADataLoaderHrefIsNotSupported(): void
    {
        $request = new Request(
            (new InvokerFactory())(),
            (new FakeRo())(new LinkedComment()),
            Method::GET,
            [],
            [new LinkType('comment-tree', LinkType::CRAWL_LINK)],
        );

        try {
            $this->linker->invoke($request);
            $this->fail('UriException expected');
        } catch (UriException) {
            $this->assertSame(0, LikeDataLoader::$callCount, 'the loader does not run');
        }
    }

    public function testDataLoaderCrawlWithoutFactory(): void
    {
        // Create linker without DataLoader factory
        $invoker = (new InvokerFactory())();
        $schemeCollection = (new SchemeCollection())
            ->scheme('app')
            ->host('self')
            ->toAdapter(new AppAdapter(new Injector(), 'FakeVendor\Sandbox'));

        $factory = new Factory($schemeCollection, new UriFactory());
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

        $linkerWithoutDataLoader = new Linker(
            $invoker,
            $factory,
            $linkCrawlerProvider,
        );

        $this->request = new Request(
            $invoker,
            (new FakeRo())(new Article()),
            Method::GET,
            ['id' => 1],
            [new LinkType('comment-tree', LinkType::CRAWL_LINK)],
        );

        // Should still work but fall back to individual requests
        $result = $linkerWithoutDataLoader->invoke($this->request);

        // Verify structure is still correct
        $this->assertSame(1, $result->body['id']);
        $this->assertArrayHasKey('comment', $result->body);

        // DataLoader should NOT be called
        $this->assertSame(0, LikeDataLoader::$callCount);
    }

    public function testDataLoaderCalledOncePerInvoke(): void
    {
        // First invoke
        $invoker = (new InvokerFactory())();
        $this->request = new Request(
            $invoker,
            (new FakeRo())(new Article()),
            Method::GET,
            ['id' => 1],
            [new LinkType('comment-tree', LinkType::CRAWL_LINK)],
        );

        $this->linker->invoke($this->request);
        $this->assertSame(1, LikeDataLoader::$callCount);

        // Second invoke - should call DataLoader again
        $this->request = new Request(
            $invoker,
            (new FakeRo())(new Article()),
            Method::GET,
            ['id' => 1],
            [new LinkType('comment-tree', LinkType::CRAWL_LINK)],
        );

        $this->linker->invoke($this->request);
        $this->assertSame(2, LikeDataLoader::$callCount);
    }

    public function testDataLoaderWithEmptyComments(): void
    {
        $invoker = (new InvokerFactory())();
        // Article 99 has no comments
        $this->request = new Request(
            $invoker,
            (new FakeRo())(new Article()),
            Method::GET,
            ['id' => 99],
            [new LinkType('comment-tree', LinkType::CRAWL_LINK)],
        );

        $result = $this->linker->invoke($this->request);

        $this->assertSame(99, $result->body['id']);
        $this->assertSame([], $result->body['comment']);
        // DataLoader is called once with empty URI list (handles gracefully)
        $this->assertSame(1, LikeDataLoader::$callCount);
    }

    public function testNestedCrawlSkipsNonMatchingAnnotationBeforeMatch(): void
    {
        $invoker = (new InvokerFactory())();
        $factory = new Factory(new SchemeCollection(), new UriFactory());
        $crawler = new LinkCrawler($invoker, $factory);
        $ro = new class extends ResourceObject {
            #[Link(rel: 'ignored', href: 'app://self/ignored', crawl: 'ignored-tree')]
            #[Link(rel: 'matched', href: 'app://self/matched', crawl: 'tree')]
            public function onGet(): static
            {
                return $this;
            }
        };
        $body = ['child' => []];
        $args = [$ro, 'get', new LinkType('tree', LinkType::CRAWL_LINK), 'child', &$body];
        $method = (new ReflectionClass($crawler))->getMethod('processNestedCrawl');

        $method->invokeArgs($crawler, $args);

        $this->assertSame(['child' => []], $body);
    }
}
