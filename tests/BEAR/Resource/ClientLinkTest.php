<?php

namespace BEAR\Resource;

use BEAR\Resource\Module\ResourceModule;
use Ray\Di\Injector;
use Sandbox\Resource\App\User;

class ClientLinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Resource
     */
    private $resource;

    protected function setUp()
    {
        parent::setUp();
        $this->resource = $GLOBALS['RESOURCE'];
        $scheme = new SchemeCollection;
        $injector = Injector::create([new ResourceModule]);
        $scheme->scheme('app')->host('self')->toAdapter(new Adapter\App($injector, 'Sandbox', 'Resource\App'));
        $this->resource->setSchemeCollection($scheme);
        $this->user = $this->resource->newInstance('app://self/link/user');
    }

    public function testNew()
    {
        $this->assertInstanceOf('\BEAR\Resource\ObjectInterface', $this->user);
    }

    public function testAnnotationLinkSelf()
    {
        $blog = $this
            ->resource
            ->get
            ->uri('app://self/link/user')
            ->withQuery(['id' => 1])
            ->linkSelf("blog")
            ->eager
            ->request()
            ->body;
        $expected = array(
            'name' => 'Aramis blog',
        );
        $this->assertSame($expected, $blog);
    }

//    public function testAnnotationLinkNew()
//    {
//        list($user, $blog) = $this
//            ->resource
//            ->get
//            ->uri('app://self/link/user')
//            ->withQuery(['id' => 1])
//            ->linkNew('blog')
//            ->eager
//            ->request()
//            ->body;
//        $expectedUser = [
//            'name' => 'Aramis',
//            'age' => 16,
//            'blog_id' => 1
//        ];
//        $expectedBlog = [
//            'name' => 'Aramis blog',
//        ];
//        $this->assertSame([$expectedUser, $expectedBlog], [$user, $blog]);
//    }
//
//    public function testAnnotationLinkCrawl()
//    {
//        $result = $this
//            ->resource
//            ->get
//            ->uri('app://self/link/user')
//            ->withQuery(['id' => 1])
//            ->linkCrawl("blog")
//            ->eager
//            ->request()
//            ->body;
//        $expected = [
//            'name' => 'Aramis',
//            'age' => 16,
//            'blog_id' => 1,
//            'blog' => ['name' => 'Aramis blog']
//        ];
//        $this->assertSame($expected, $result);
//    }
//
//    public function testAnnotationLinkCrawlAll()
//    {
//        $result = $this
//            ->resource
//            ->get
//            ->uri('app://self/link/user')
//            ->linkCrawl("blog")
//            ->eager
//            ->request()
//            ->body;
//        $expected = [
//            [
//                'name' => 'Athos',
//                'age' => 15,
//                'blog_id' => 0,
//                'blog' => ['name' => 'Athos blog']
//            ],
//            [
//                'name' => 'Aramis',
//                'age' => 16,
//                'blog_id' => 1,
//                'blog' => ['name' => 'Aramis blog']
//            ],
//            [
//                'name' => 'Porthos',
//                'age' => 17,
//                'blog_id' => 2,
//                'blog' => ['name' => 'Porthos blog']
//            ]
//        ];
//        $this->assertSame($expected, $result);
//    }
//
//    public function testMethodLinkSelf()
//    {
//        $result = $this
//            ->resource
//            ->get
//            ->uri('app://self/link')
//            ->withQuery(['id' => 1])
//            ->linkSelf("view")
//            ->eager
//            ->request()
//            ->body;
//        $expected = '<html>bear1</html>';
//        $this->assertSame($expected, $result);
//    }
//
//    public function testMethodLinkNew()
//    {
//        $ro = clone $this->user;
//        list($user, $blog) = $this->resource
//            ->get
//            ->object($ro)
//            ->withQuery(['id' => 1])
//            ->linkNew('blog')
//            ->eager
//            ->request()
//            ->body;
//        $expected = [
//            [
//                'id' => 2,
//                'name' => 'Aramis',
//                'age' => 16,
//                'blog_id' => 12
//            ],
//            [
//                'id' => 12,
//                'name' => 'Aramis blog',
//                'inviter' => 2
//            ]
//        ];
//        $this->assertSame($expected, [$user, $blog]);
//    }
//    public function testMethodLinkCrawl()
//    {
//        $ro = clone $this->user;
//        $result = $this->resource
//            ->get
//            ->object($ro)
//            ->linkCrawl('tree')
//            ->eager
//            ->request()
//            ->body;
//        $expected = [
//            'id' => 1,
//            'name' => 'Athos',
//            'age' => 15,
//            'blog_id' => 11,
//            'tag' => 'tag1'
//        ];
//        $this->assertSame($expected, $result);
//    }
}
