<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\LinkQueryException;
use BEAR\Resource\Exception\LinkRelException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
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
        $this->invoker = new Invoker(new NamedParameter(new ArrayCache, new AnnotationReader, new Injector), new OptionsRenderer(new AnnotationReader()));
        $schemeCollection = (new SchemeCollection)
            ->scheme('app')
            ->host('self')
            ->toAdapter(new AppAdapter(new Injector, 'FakeVendor\Sandbox', 'Resource\App'));
        $this->linker = new Linker(
            new AnnotationReader,
            $this->invoker,
            new Factory($schemeCollection)
        );
    }

    public function testLinkAnnotationSelf()
    {
        $request = new Request(
            $this->invoker,
            new Author,
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
            new Author,
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
            new Blog,
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

    public function testScalarValueLinkThrowException()
    {
        $this->setExpectedException(LinkQueryException::class);
        $request = new Request(
            $this->invoker,
            new Name,
            Request::GET,
            ['name' => 'bear'],
            [new LinkType('blog', LinkType::SELF_LINK)]
        );
        $this->linker->invoke($request);
    }

    public function testInvalidRel()
    {
        $this->setExpectedException(LinkRelException::class);
        $request = new Request(
            $this->invoker,
            new Author,
            Request::GET,
            ['id' => '1'],
            [new LinkType('invalid-link', LinkType::SELF_LINK)]
        );
        $this->linker->invoke($request);
    }
}
