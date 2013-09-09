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
        $this->assertInstanceOf('\BEAR\Resource\ResourceObject', $this->user);
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

    public function testAnnotationLinkNew()
    {
        list($user, $blog) = $this
            ->resource
            ->get
            ->uri('app://self/link/user')
            ->withQuery(['id' => 1])
            ->linkNew('blog')
            ->eager
            ->request()
            ->body;
        $expectedUser = [
            'name' => 'Aramis',
            'age' => 16,
            'blog_id' => 12
        ];
        $expectedBlog = [
            'name' => 'Aramis blog',
        ];
        $this->assertSame([$expectedUser, $expectedBlog], [$user, $blog]);
    }

    public function testAnnotationLinkCrawl()
    {
        $result = $this
            ->resource
            ->get
            ->uri('app://self/marshal/author')
            ->withQuery(['id' => 1])
            ->linkCrawl("tree")
            ->eager
            ->request()
            ->body;

        $this->assertTrue(isset($result['post'][0]['meta']));
        $this->assertTrue(isset($result['post'][0]['tag']));
        $this->assertTrue(isset($result['post'][0]['tag'][0]['tag_name']));
    }
}