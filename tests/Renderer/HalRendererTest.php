<?php

namespace BEAR\Resource\Renderer;

use Aura\Signal\HandlerFactory;
use Aura\Signal\Manager;
use Aura\Signal\ResultCollection;
use Aura\Signal\ResultFactory;
use BEAR\Resource\Invoker;
use BEAR\Resource\Linker;
use BEAR\Resource\Logger;
use BEAR\Resource\NamedParameter;
use BEAR\Resource\Param;
use BEAR\Resource\Request;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\SignalParameter;
use BEAR\Resource\UriMapper;
use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Di\Injector;
use BEAR\Resource\Module\ResourceModule;

class MockResource extends ResourceObject
{
    public $uri = 'app://self/mock';

    public $headers = ['head1' => 1];

    public $body = [
        'greeting' => 'hello'
    ];

    public function onGet($a, $b)
    {
        $this['posts'] = [$a, $b];

        return $this;
    }
}

class HalRendererTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var HalRenderer
     */
    private $halRenderer;

    /**
     * @var ResourceObject
     */
    private $resource;

    protected function setUp()
    {
        $this->halRenderer = new HalRenderer(new UriMapper('api'));
        $this->resource = new MockResource;
        $this->resource->uri = 'dummy://self/index';

    }

    public function testNew()
    {
        $this->assertInstanceOf('\BEAR\Resource\Renderer\HalRenderer', $this->halRenderer);
    }

    public function testRender()
    {
        $this->resource->setRenderer($this->halRenderer);
        $this->halRenderer->render($this->resource);
        $this->assertSame("application/hal+json; charset=UTF-8", $this->resource->headers['content-type']);

        return $this->resource;
    }

    /**
     * @depends testRender
     */
    public function testRenderView(ResourceObject $resource)
    {
        $this->assertContains('"greeting": "hello"', $resource->view);
    }

    public function testRenderBodyIsScalar()
    {
        $this->resource->body = 'hello';
        $this->resource->setRenderer($this->halRenderer);
        $this->halRenderer->render($this->resource);
        $this->assertContains('"value": "hello"', $this->resource->view);
    }

    public function testRenderHasLink()
    {
        $this->resource->links = ['rel1' => ['href' => 'page://self/rel1']];
        $this->resource->setRenderer($this->halRenderer);
        $this->halRenderer->render($this->resource);
        $links = '"_links": {
        "self": {
            "href": "/api/index"
        },
        "rel1": {
            "href": "/api/rel1"
        }
    }';
        $this->assertContains($links, $this->resource->view);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\HrefNotFound
     */
    public function testRenderInvalidLink()
    {
        $this->resource->links = ['rel1' => 'page://self/rel1'];
        $this->resource->setRenderer($this->halRenderer);
        $this->halRenderer->render($this->resource);

    }

    public function testBodyHasRequest()
    {
        $invoker = new Invoker(
            new Linker(new AnnotationReader),
            new NamedParameter(
                new SignalParameter(
                    new Manager(new HandlerFactory, new ResultFactory, new ResultCollection),
                    new Param
                )
            ),
            new Logger
        );
        $request = new Request($invoker);
        $request->set(new MockResource, 'nop://mock', 'get', ['a'=>1, 'b'=>2]);
        $this->resource->body['req'] = $request;
        $this->resource->setRenderer($this->halRenderer);
        $this->halRenderer->render($this->resource);
        $this->assertContains('"greeting": "hello"', $this->resource->view);
    }

    public function testEmbedResource()
    {
        $resource = Injector::create([new ResourceModule('TestVendor\Sandbox')])->getInstance('BEAR\Resource\ResourceInterface');
        $resourceObject = $resource
            ->get
            ->uri('app://self/bird/birds')
            ->withQuery(['id' => 1])
            ->eager
            ->request();
        $resourceObject->setRenderer($this->halRenderer);
        $hal = (string) $resourceObject;
        $this->assertSame('{
    "_links": {
        "self": {
            "href": "/api/bird/birds?id=1"
        }
    },
    "_embedded": {
        "bird1": [
            {
                "name": "chill kun",
                "_links": {
                    "self": {
                        "href": "/api/bird/canary"
                    }
                }
            }
        ],
        "bird2": [
            {
                "sparrow_id": "1",
                "_links": {
                    "self": {
                        "href": "/api/bird/sparrow?id=1"
                    }
                }
            }
        ]
    }
}', $hal);
    }
}
