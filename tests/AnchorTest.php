<?php

namespace BEAR\Resource;

use Doctrine\Common\Annotations\AnnotationReader;
use FakeVendor\Sandbox\Resource\App\Link\User;
use BEAR\Resource\Exception\Link;

class AnchorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Anchor
     */
    private $anchor;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Resource
     */
    private $resource;

    protected function setUp()
    {
        parent::setUp();
        $invoker = new Invoker(new Linker(new AnnotationReader), new NamedParameter);
        $ro = new User;
        $ro->body['blog_id'] = 1;
        $this->request = new Request($invoker, $ro);
        $this->anchor = new Anchor(new AnnotationReader, $this->request);
    }

    public function testHref()
    {
        list($method, $uri) = $this->anchor->href('blog', $this->request, []);
        $this->assertSame(Request::GET, $method);
        $this->assertSame('app://self/link/blog?id=1',$uri);
    }

    public function testInvalid()
    {
        $this->setExpectedException(Link::class);
        $this->anchor->href('invalid', $this->request, []);

    }
}
