<?php

declare(strict_types=1);

namespace BEAR\Resource\Renderer;

use BEAR\Resource\FakeRoot;
use BEAR\Resource\JsonRenderer;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\Uri;
use function file_get_contents;
use PHPUnit\Framework\TestCase;

class JsonRendererTest extends TestCase
{
    /**
     * @var FakeRoot
     */
    private $ro;

    protected function setUp() : void
    {
        $this->ro = new FakeRoot;
        $this->ro->uri = new Uri('app://self/dummy');
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
        $log = ini_get('error_log');
        $logFile = dirname(__DIR__) . '/log/error.log';
        ini_set('error_log', $logFile);
        $this->ro['inf'] = log(0);
        $data = (string) $this->ro;
        $this->assertInternalType('string', $data);
        ini_set('error_log', $log);
        $this->assertContains('json_encode error', (string) file_get_contents($logFile));
    }

    public function testHeader()
    {
        /* @var $ro ResourceObject */
        $ro = $this->ro->onGet();
        (string) $ro;
        $expected = 'application/json';
        $this->assertSame($expected, $ro->headers['Content-Type']);
    }
}
