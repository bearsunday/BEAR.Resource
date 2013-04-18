<?php

namespace BEAR\Resource;

use BEAR\Resource\Adapter\App;
use BEAR\Resource\Adapter\Http;
use Ray\Di\Injector;

class scriptTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BEAR\Resource\Resource
     */
    private $resource;


    protected function setUp()
    {
        parent::setUp();
        $this->resource = require dirname(dirname(dirname(__DIR__))) . '/scripts/instance.php';
    }

    public function test_New()
    {
        $this->assertInstanceOf('\BEAR\Resource\Resource', $this->resource);
    }

    public function test_setSchemeHttp()
    {
        $scheme = (new SchemeCollection)->scheme('http')->host('*')->toAdapter(new Http);
        $this->resource->setSchemeCollection($scheme);
        $result = $this->resource->get->uri('http://rss.excite.co.jp/rss/excite/odd')->eager->request();
        $this->assertSame(200, $result->code);
    }

    public function test_setSchemeApp()
    {
        $app = new App(Injector::create([]), 'Space', 'Resource\App');
        $scheme = (new SchemeCollection)->scheme('app')->host('self')->toAdapter($app);
        $this->resource->setSchemeCollection($scheme);
        $result = $this->resource->get->uri('app://self/index')->eager->request();
        $this->assertSame('get', $result->body);
    }
}
