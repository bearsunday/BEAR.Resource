<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use PHPUnit\Framework\TestCase;

class JsonRendererTest extends TestCase
{
    /**
     * @var
     */
    private $ro;

    protected function setUp()
    {
        $this->ro = new FakeRoot;
        $this->ro->setRenderer(new JsonRenderer);
    }

    public function testRender()
    {
        $ro = $this->ro->onGet();
        $data = (string) $ro;
        $expected = '{"one":1,"two":{"tree":3}}';
        $this->assertSame($expected, $data);
    }

    public function testRenderScalar()
    {
        $this->ro->body = 1;
        $data = (string) $this->ro;
        $expected = '{"value":1}';
        $this->assertSame($expected, $data);
    }

    public function testError()
    {
        $this->ro['inf'] = log(0);
        $data = (string) $this->ro;
        $this->assertInternalType('string', $data);
    }

    public function testHeader()
    {
        /* @var $ro ResourceObject */
        $ro = $this->ro->onGet();
        (string) $ro;
        $expected = 'application/json';
        $this->assertSame($expected, $ro->headers['content-type']);
    }
}
