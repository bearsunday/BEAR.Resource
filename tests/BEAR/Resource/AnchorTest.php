<?php

namespace BEAR\Resource;

use Guzzle\Parser\UriTemplate\UriTemplate;
use Aura\Signal\Manager;
use Aura\Signal\HandlerFactory;
use Aura\Signal\ResultFactory;
use Aura\Signal\ResultCollection;
use Ray\Di\Definition;
use Ray\Di\Injector;
use Doctrine\Common\Annotations\AnnotationReader;


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
        $this->resource = clone $GLOBALS['resource'];

        $signal = new Manager(new HandlerFactory, new ResultFactory, new ResultCollection);
        $params = new NamedParams(new SignalParam($signal, new Param));
        $invoker = new Invoker(new Linker(new AnnotationReader), $params);
        $this->request = new Request($invoker);

        $this->anchor = new Anchor(new UriTemplate, new AnnotationReader, $this->request);
    }

    public function testNew()
    {
        $this->assertInstanceOf('\BEAR\Resource\Anchor', $this->anchor);
    }

    public function testHref()
    {
        $this->resource->get->uri('app://self/link/user')->withQuery(['id' => 0])->eager->request();

        $query = [];
        $blog = $this->resource->href('blog', $query);

        $this->assertInstanceOf('\Sandbox\Resource\App\Link\Blog', $blog);
        $this->assertSame(['name' => "Athos blog"], $blog->body);

        return $this->resource;
    }

    public function testHrefOverRide()
    {
        $this->resource->get->uri('app://self/link/user')->withQuery(['id' => 0])->eager->request();
        $query = ['blog_id' => 99];
        $blog = $this->resource->href('blog', $query);

        $this->assertInstanceOf('\Sandbox\Resource\App\Link\Blog', $blog);
        $this->assertSame(['name' => "BEAR blog"], $blog->body);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\Link
     */
    public function testInvalidHref()
    {
        $this->resource->get->uri('app://self/link/user')->withQuery(['id' => 0])->eager->request();
        $this->resource->href('xxx', []);
    }
}
