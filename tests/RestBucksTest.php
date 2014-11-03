<?php

namespace BEAR\Resource;

use BEAR\Resource\Adapter\App;
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

        $this->resource = require $_ENV['PACKAGE_DIR'] . '/scripts/instance.php';
        $injector = new Injector;
        $scheme = new SchemeCollection;
        $scheme->scheme('app')->host('self')->toAdapter(new App($injector, 'TestVendor\Sandbox', 'Resource\App'));
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
