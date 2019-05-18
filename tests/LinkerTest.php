<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\LinkQueryException;
use BEAR\Resource\Exception\LinkRelException;
use Doctrine\Common\Annotations\AnnotationReader;
use FakeVendor\Sandbox\Resource\App\Author;
use FakeVendor\Sandbox\Resource\App\Blog;
use FakeVendor\Sandbox\Resource\App\Link\Scalar\Name;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class LinkerTest extends TestCase
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Linker
     */
    private $linker;

    /**
     * @var Invoker
     */
    private $invoker;

    protected function setUp()
    {
        parent::setUp();
        $this->invoker = (new InvokerFactory)();
        $schemeCollection = (new SchemeCollection)
            ->scheme('app')
            ->host('self')
            ->toAdapter(new AppAdapter(new Injector, 'FakeVendor\Sandbox'));
        $this->linker = new Linker(
            new AnnotationReader,
            $this->invoker,
            new Factory($schemeCollection, new UriFactory)
        );
    }

    public function testLinkAnnotationSelf()
    {
        $request = new Request(
            $this->invoker,
            (new FakeRo)(new Author),
            Request::GET,
            ['id' => 1],
            [new LinkType('blog', LinkType::SELF_LINK)]
        );
        $result = $this->linker->invoke($request);
        $expected = [
            'id' => 12,
            'name' => 'Aramis blog'
        ];
        $this->assertSame($expected, $result->body);
    }

    public function testAnnotationNew()
    {
        $request = new Request(
            $this->invoker,
            (new FakeRo)(new Author),
            Request::GET,
            ['id' => 1],
            [new LinkType('blog', LinkType::NEW_LINK)]
        );
        $result = $this->linker->invoke($request);
        $expected = [
            'name' => 'Aramis',
            'age' => 16,
            'blog_id' => 12,
            'blog' => [
                'id' => 12,
                'name' => 'Aramis blog'
            ]
        ];
        $this->assertSame($expected, $result->body);
    }

    public function testAnnotationCrawl()
    {
        $request = new Request(
            $this->invoker,
            (new FakeRo)(new Blog),
            Request::GET,
            ['id' => 11],
            [new LinkType('tree', LinkType::CRAWL_LINK)]
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
        $this->assertSame($expected, $result->body);
    }

    public function testAnnotationCrawl2()
    {
        $request = new Request(
            $this->invoker,
            (new FakeRo)(new Blog),
            Request::GET,
            ['id' => 16],
            [new LinkType('tree', LinkType::CRAWL_LINK)]
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
                            'tag_type' => [
                                'type1',
                            ],
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
                            'tag_type' => [
                                'type1',
                            ],
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
                            'tag_type' => [
                                'type1',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertSame($expected, $result->body);
    }

    public function testAnnotationCrawl3()
    {
        $request = new Request(
            $this->invoker,
            (new FakeRo)(new Blog),
            Request::GET,
            ['id' => 17],
            [new LinkType('tree', LinkType::CRAWL_LINK)]
        );
        $result = $this->linker->invoke($request);
        $expected = [
            'id' => 17,
            'name' => 'My blog',
            'label' => [
                'a',
                'b'
            ],
            'keyword' => [
                'c',
                'd'
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

    public function testScalarValueLinkThrowException()
    {
        $this->expectException(LinkQueryException::class);
        $request = new Request(
            $this->invoker,
            (new FakeRo)(new Name),
            Request::GET,
            ['name' => 'bear'],
            [new LinkType('blog', LinkType::SELF_LINK)]
        );
        $this->linker->invoke($request);
    }

    public function testInvalidRel()
    {
        $this->expectException(LinkRelException::class);
        $request = new Request(
            $this->invoker,
            (new FakeRo)(new Author),
            Request::GET,
            ['id' => '1'],
            [new LinkType('invalid-link', LinkType::SELF_LINK)]
        );
        $this->linker->invoke($request);
    }
}
