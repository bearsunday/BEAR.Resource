<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\LinkException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use FakeVendor\Sandbox\Resource\App\Author;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class AnchorTest extends TestCase
{
    /**
     * @var Anchor
     */
    private $anchor;

    /**
     * @var Request
     */
    private $request;

    protected function setUp()
    {
        parent::setUp();
        $invoker = new Invoker(new NamedParameter(new ArrayCache, new AnnotationReader, new Injector), new OptionsRenderer(new AnnotationReader()));
        $author = new Author;
        $author->onGet(1);
        $this->request = new Request($invoker, $author, Request::GET, ['id' => 1]);
        $this->anchor = new Anchor(new AnnotationReader, $this->request);
    }

    public function testHref()
    {
        list($method, $uri) = $this->anchor->href('blog', $this->request, []);
        $this->assertSame(Request::GET, $method);
        $this->assertSame('app://self/blog?id=12', $uri);
    }

    public function testInvalid()
    {
        $this->setExpectedException(LinkException::class);
        $this->anchor->href('invalid', $this->request, []);
    }
}
