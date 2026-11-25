<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\LinkHrefException;
use BEAR\Resource\Exception\LinkQueryException;
use BEAR\Resource\Exception\LinkRelException;
use FakeVendor\Sandbox\Resource\App\Author;
use FakeVendor\Sandbox\Resource\App\Blog;
use FakeVendor\Sandbox\Resource\App\Link\HrefDeclaredFragment;
use FakeVendor\Sandbox\Resource\App\Link\Jump\First;
use FakeVendor\Sandbox\Resource\App\Link\Jump\Second;
use FakeVendor\Sandbox\Resource\App\Link\Jump\Soft400;
use FakeVendor\Sandbox\Resource\App\Link\Jump\Soft404;
use FakeVendor\Sandbox\Resource\App\Link\Jump\Soft500;
use FakeVendor\Sandbox\Resource\App\Link\Jump\Start;
use FakeVendor\Sandbox\Resource\App\Link\Scalar\Name;
use FakeVendor\Sandbox\Resource\App\Marshal\NullBody;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;
use Ray\Di\ProviderInterface;
use ReflectionClass;

class LinkerTest extends TestCase
{
    protected Request $request;
    private Linker $linker;
    private Invoker $invoker;

    protected function setUp(): void
    {
        $this->invoker = (new InvokerFactory())();
        $schemeCollection = (new SchemeCollection())
            ->scheme('app')
            ->host('self')
            ->toAdapter(new AppAdapter(new Injector(), 'FakeVendor\Sandbox'));
        $factory = new Factory($schemeCollection, new UriFactory());
        $invoker = $this->invoker;
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
        $this->linker = new Linker(
            $this->invoker,
            $factory,
            $linkCrawlerProvider,
        );
    }

    public function testLinkAnnotationSelf(): void
    {
        $request = new Request(
            $this->invoker,
            (new FakeRo())(new Author()),
            Method::GET,
            ['id' => 1],
            [new LinkType('blog', LinkType::SELF_LINK)],
        );
        $result = $this->linker->invoke($request);
        $expected = [
            'id' => 12,
            'name' => 'Aramis blog',
        ];
        $this->assertSame($expected, $result->body);
    }

    public function testAnnotationNew(): void
    {
        $request = new Request(
            $this->invoker,
            (new FakeRo())(new Author()),
            Method::GET,
            ['id' => 1],
            [new LinkType('blog', LinkType::NEW_LINK)],
        );
        $result = $this->linker->invoke($request);
        $expected = [
            'name' => 'Aramis',
            'age' => 16,
            'blog_id' => 12,
            'blog' => [
                'id' => 12,
                'name' => 'Aramis blog',
            ],
        ];
        $this->assertSame($expected, $result->body);
    }

    public function testAnnotationCrawl(): void
    {
        $request = new Request(
            $this->invoker,
            (new FakeRo())(new Blog()),
            Method::GET,
            ['id' => 11],
            [new LinkType('tree', LinkType::CRAWL_LINK)],
        );
        $result = $this->linker->invoke($request);
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
                        'data' => 'meta 1',
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
                                'name' => 'zim',
                            ],
                        ],
                        'tag_type' => [0 => 'type1'],
                    ],
                    1 => [
                        'id' => '2',
                        'post_id' => '1',
                        'tag_id' => '2',
                        'tag_name' => [
                            0 => [
                                'id' => '2',
                                'name' => 'dib',
                            ],
                        ],
                        'tag_type' => [0 => 'type1'],
                    ],
                ],
            ],
        ];
        $this->assertSame($expected, $result->body);
    }

    public function testAnnotationCrawl2(): void
    {
        $request = new Request(
            $this->invoker,
            (new FakeRo())(new Blog()),
            Method::GET,
            ['id' => 16],
            [new LinkType('tree', LinkType::CRAWL_LINK)],
        );
        $result = $this->linker->invoke($request);
        $expected = [
            'id' => 16,
            'name' => 'Porthos blog',
            'post' => [
                [
                    'id' => '6',
                    'author_id' => '3',
                    'body' => 'Porthos post #1',
                    'meta' => [
                        [
                            'id' => '6',
                            'post_id' => '6',
                            'data' => 'meta 6',
                        ],
                    ],
                    'tag' => [
                        [
                            'id' => '11',
                            'post_id' => '6',
                            'tag_id' => '1',
                            'tag_name' => [
                                [
                                    'id' => '1',
                                    'name' => 'zim',
                                ],
                            ],
                            'tag_type' => ['type1'],
                        ],
                    ],
                ],
                [
                    'id' => '7',
                    'author_id' => '3',
                    'body' => 'Porthos post #1',
                    'meta' => [
                        [
                            'id' => '7',
                            'post_id' => '7',
                            'data' => 'meta 7',
                        ],
                    ],
                    'tag' => [
                        [
                            'id' => '12',
                            'post_id' => '7',
                            'tag_id' => '2',
                            'tag_name' => [
                                [
                                    'id' => '2',
                                    'name' => 'dib',
                                ],
                            ],
                            'tag_type' => ['type1'],
                        ],
                    ],
                ],
                [
                    'id' => '8',
                    'author_id' => '3',
                    'body' => 'Porthos post #1',
                    'meta' => [
                        [
                            'id' => '8',
                            'post_id' => '8',
                            'data' => 'meta 8',
                        ],
                    ],
                    'tag' => [
                        [
                            'id' => '13',
                            'post_id' => '8',
                            'tag_id' => '3',
                            'tag_name' => [
                                [
                                    'id' => '3',
                                    'name' => 'gir',
                                ],
                            ],
                            'tag_type' => ['type1'],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertSame($expected, $result->body);
    }

    public function testAnnotationCrawl3(): void
    {
        $request = new Request(
            $this->invoker,
            (new FakeRo())(new Blog()),
            Method::GET,
            ['id' => 17],
            [new LinkType('tree', LinkType::CRAWL_LINK)],
        );
        $result = $this->linker->invoke($request);
        $expected = [
            'id' => 17,
            'name' => 'My blog',
            'label' => [
                'a',
                'b',
            ],
            'keyword' => [
                'c',
                'd',
            ],
            'post' => [
                'id' => '9',
                'author_id' => '4',
                'body' => 'My post #1',
                'meta' => [],
                'tag' => [],
            ],
        ];
        $this->assertSame($expected, $result->body);
    }

    public function testScalarValueLinkThrowException(): void
    {
        $this->expectException(LinkQueryException::class);
        $request = new Request(
            $this->invoker,
            (new FakeRo())(new Name()),
            Method::GET,
            ['name' => 'bear'],
            [new LinkType('blog', LinkType::SELF_LINK)],
        );
        $this->linker->invoke($request);
    }

    public function testInvalidRel(): void
    {
        $this->expectException(LinkRelException::class);
        $request = new Request(
            $this->invoker,
            (new FakeRo())(new Author()),
            Method::GET,
            ['id' => '1'],
            [new LinkType('invalid-link', LinkType::SELF_LINK)],
        );
        $this->linker->invoke($request);
    }

    public function testNotFoundResourceHasBody(): void
    {
        $request = new Request(
            $this->invoker,
            (new FakeRo())(new Blog\NotFound()),
            Method::GET,
            [],
            [new LinkType('meta', LinkType::CRAWL_LINK)],
        );
        $this->linker->invoke($request);
        $this->assertSame(['message' => 'blog not found'], $request->body);

        $request = new Request(
            $this->invoker,
            (new FakeRo())(new Blog\NotFound()),
            Method::GET,
            [],
            [new LinkType('user', LinkType::SELF_LINK)],
        );
        $this->linker->invoke($request);
        $this->assertSame(['message' => 'blog not found'], $request->body);

        $request = new Request(
            $this->invoker,
            (new FakeRo())(new Blog\NotFound()),
            Method::GET,
            [],
            [new LinkType('user', LinkType::NEW_LINK)],
        );
        $this->linker->invoke($request);
        $this->assertSame(['message' => 'blog not found'], $request->body);
    }

    public function testCrawlWithNullBody(): void
    {
        $request = new Request(
            $this->invoker,
            (new FakeRo())(new NullBody()),
            Method::GET,
            ['id' => 1],
            [new LinkType('tree', LinkType::CRAWL_LINK)],
        );
        $result = $this->linker->invoke($request);
        $expected = [
            'id' => 1,
            'name' => 'test',
            'child' => null,
        ];
        $this->assertSame($expected, $result->body);
    }

    public function testAnnotationCrawlWithListBody(): void
    {
        $current = new class extends ResourceObject {
        };
        $current->uri = new Uri('app://self/list-body');
        $current->body = [
            ['id' => 1],
        ];
        $linkCrawler = new class implements LinkCrawlerInterface {
            public function crawl(array $annotations, LinkType $link, array &$bodyList): void
            {
                $bodyList[0]['linked'] = true;
            }

            public function isList(mixed $value): bool
            {
                return true;
            }
        };

        $result = $this->invokePrivate(
            $this->linker,
            'annotationCrawl',
            [[], new LinkType('tree', LinkType::CRAWL_LINK), $current, $linkCrawler],
        );

        $this->assertSame([['id' => 1, 'linked' => true]], $result->body);
    }

    public function testLinkSelfToALinkThatDeclaresAFragmentThrows(): void
    {
        $request = new Request(
            $this->invoker,
            (new FakeRo())(new HrefDeclaredFragment()),
            Method::GET,
            [],
            [new LinkType('next', LinkType::SELF_LINK)],
        );

        $this->expectException(LinkHrefException::class);
        $this->expectExceptionMessage('/author?id=1#top: a #[Link] href cannot have a fragment');

        $this->linker->invoke($request);
    }

    public function testLinkSelfReturnsTheLinkedResource(): void
    {
        $result = $this->linker->invoke($this->startRequest(Method::GET, [new LinkType('first', LinkType::SELF_LINK)]));

        $this->assertInstanceOf(First::class, $result);
        $this->assertSame('app://self/link/jump/first?id=1', (string) $result->uri);
        $this->assertSame(Code::ACCEPTED, $result->code);
        $this->assertSame(['ETag' => 'first'], $result->headers);
        $this->assertSame('first view', $result->view);
        $this->assertSame(['id' => 1, 'name' => 'first'], $result->body);
    }

    public function testASelfLinkChainLooksUpEachRelOnTheResourceTheLastLinkReturned(): void
    {
        $result = $this->linker->invoke($this->startRequest(Method::GET, [
            new LinkType('first', LinkType::SELF_LINK),
            new LinkType('second', LinkType::SELF_LINK),
        ]));

        $this->assertInstanceOf(Second::class, $result);
        $this->assertSame('second view', $result->view);
        $this->assertSame(['id' => 1, 'name' => 'second'], $result->body);
    }

    public function testAResourceASelfLinkReturnsHasItsLinksReadFromOnGet(): void
    {
        $result = $this->linker->invoke($this->startRequest(Method::POST, [
            new LinkType('posted', LinkType::SELF_LINK),
            new LinkType('second', LinkType::SELF_LINK),
        ]));

        $this->assertInstanceOf(Second::class, $result);
    }

    /** @return array<string, array{Method, string}> */
    public static function relOfTheOtherMethodProvider(): array
    {
        return [
            'POST, a rel only onGet() declares' => [Method::POST, 'first'],
            'GET, a rel only onPost() declares' => [Method::GET, 'posted'],
        ];
    }

    #[DataProvider('relOfTheOtherMethodProvider')]
    public function testTheFirstRelIsReadFromTheRequestedMethod(Method $method, string $rel): void
    {
        $this->expectException(LinkRelException::class);

        $this->linker->invoke($this->startRequest($method, [new LinkType($rel, LinkType::SELF_LINK)]));
    }

    /** @return array<string, array{list<LinkType>, class-string<ResourceObject>, int, array<string, int|string>}> */
    public static function selfLinkToAnErrorProvider(): array
    {
        return [
            '400, then self' => [
                [new LinkType('soft400', LinkType::SELF_LINK), new LinkType('second', LinkType::SELF_LINK)],
                Soft400::class,
                Code::BAD_REQUEST,
                ['id' => 1, 'name' => 'soft400'],
            ],
            '404, then self' => [
                [new LinkType('soft404', LinkType::SELF_LINK), new LinkType('second', LinkType::SELF_LINK)],
                Soft404::class,
                Code::NOT_FOUND,
                ['id' => 1, 'name' => 'soft404'],
            ],
            '404, then new' => [
                [new LinkType('soft404', LinkType::SELF_LINK), new LinkType('second', LinkType::NEW_LINK)],
                Soft404::class,
                Code::NOT_FOUND,
                ['id' => 1, 'name' => 'soft404'],
            ],
            '500, then self' => [
                [new LinkType('soft500', LinkType::SELF_LINK), new LinkType('first', LinkType::SELF_LINK)],
                Soft500::class,
                Code::ERROR,
                ['id' => 1, 'name' => 'soft500'],
            ],
        ];
    }

    /**
     * @param list<LinkType>               $links
     * @param class-string<ResourceObject> $class
     * @param array<string, int|string>    $body
     */
    #[DataProvider('selfLinkToAnErrorProvider')]
    public function testASelfLinkToAnErrorStopsTheChain(array $links, string $class, int $code, array $body): void
    {
        $result = $this->linker->invoke($this->startRequest(Method::GET, $links));

        $this->assertInstanceOf($class, $result);
        $this->assertSame($code, $result->code);
        $this->assertSame($body, $result->body);
    }

    public function testNewAndCrawlAfterASelfLinkAddToTheLinkedResource(): void
    {
        $second = ['id' => 1, 'name' => 'second'];
        foreach ([LinkType::NEW_LINK => 'second', LinkType::CRAWL_LINK => 'next'] as $type => $key) {
            $result = $this->linker->invoke($this->startRequest(Method::GET, [
                new LinkType('first', LinkType::SELF_LINK),
                new LinkType($key, $type),
            ]));

            $this->assertInstanceOf(First::class, $result);
            $this->assertSame('first view', $result->view);
            $this->assertSame(['id' => 1, 'name' => 'first', 'second' => $second], $result->body);
        }
    }

    public function testNewAndCrawlAddToTheRequestedResource(): void
    {
        $first = ['id' => 1, 'name' => 'first'];
        foreach ([LinkType::NEW_LINK => 'first', LinkType::CRAWL_LINK => 'tree'] as $type => $key) {
            $result = $this->linker->invoke($this->startRequest(Method::GET, [new LinkType($key, $type)]));

            $this->assertInstanceOf(Start::class, $result);
            $this->assertSame(Code::OK, $result->code);
            $this->assertSame(['ETag' => 'start'], $result->headers);
            $this->assertSame('start view', $result->view);
            $this->assertSame(['id' => 1, 'name' => 'start', 'first' => $first], $result->body);
        }
    }

    /** @param list<LinkType> $links */
    private function startRequest(Method $method, array $links): Request
    {
        $start = new Start();
        $start->uri = new Uri('app://self/link/jump/start');
        $start->uri->method = $method->value;

        return new Request($this->invoker, $start, $method, ['id' => 1], $links);
    }

    /** @param list<mixed> $args */
    private function invokePrivate(object $object, string $method, array $args): mixed
    {
        $refMethod = (new ReflectionClass($object))->getMethod($method);

        return $refMethod->invokeArgs($object, $args);
    }
}
