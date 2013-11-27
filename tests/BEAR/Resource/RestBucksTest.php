<?php

namespace BEAR\Resource;

use BEAR\Resource\Adapter\App;
use BEAR\Resource\SchemeCollection;
use Guzzle\Parser\UriTemplate\UriTemplate;
use Ray\Di\Definition;
use Ray\Di\Injector;

class RestBucksTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Resource
     */
    private $resource;

    protected function setUp()
    {
        parent::setUp();

        $this->resource = require dirname(dirname(dirname(__DIR__))) . '/scripts/instance.php';
        $injector = Injector::create();
        $scheme = new SchemeCollection;
        $scheme->scheme('app')->host('self')->toAdapter(new App($injector, 'Sandbox', 'Resource\App'));
        $this->resource->setSchemeCollection($scheme);
    }

    public function testNew()
    {
        $this->assertInstanceOf('\BEAR\Resource\Resource', $this->resource);
    }

    public function testOption()
    {
        $allow = $this->resource->options->uri('app://self/restbucks/menu')->eager->request()->headers['allow'];
        asort($allow);
        $expected = ['get'];
        $this->assertSame($expected, $allow);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\MethodNotAllowed
     */
    public function testOptionDelete()
    {
        $this->resource->delete->uri('app://self/restbucks/menu')->eager->request()->body;
    }

    public function tesMenuLinksOrder()
    {
        $menu = $this->resource->get->uri('app://self/restbucks/menu')->withQuery(array('drink' => 'latte'))->eager->request();
        $orderUri = $menu->links['order'];
        $response = $this->resource->post->uri($orderUri)->addQuery(array('drink' => $menu['drink']))->eager->request();
        $expected = 201;
        $this->assertSame($expected, $response->code);
        $expected = 'app://self/order/?id=1234';
        $this->assertSame($expected, $response->headers['Location']);
    }
}
