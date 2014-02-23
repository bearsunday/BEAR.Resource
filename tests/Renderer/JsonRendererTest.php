<?php

namespace BEAR\Resource\Renderer;

use BEAR\Resource\ResourceObject;
use Ray\Di\Definition;

class Root extends ResourceObject
{
    public function onGet()
    {
        $this['one'] = 1;
        $this['two'] = $GLOBALS['RESOURCE']->get->object(new Child)->request();
        return $this;
    }
}

class Child extends ResourceObject
{
    public function onGet()
    {
        $this['tree'] = 3;
        return $this;
    }
}


class JsonRendererTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->ro = new Root;
        $this->ro->setRenderer(new JsonRenderer);
    }

    public function testRender()
    {
        $ro = $this->ro->onGet();
        $data = (string)$ro;
        $expected = '{"one":1,"two":{"tree":3}}';
        $this->assertSame($expected, $data);
    }

    public function testRenderScalar()
    {
        $this->ro->body = 1;
        $data = (string)$this->ro;
        $expected = '1';
        $this->assertSame($expected, $data);
    }

    public function testError()
    {
        $this->ro['inf'] = log(0);
        $data = (string)$this->ro;
        $this->assertInternalType('string', $data);
    }
}
