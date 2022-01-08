<?php

declare(strict_types=1);

namespace BEAR\Resource\Renderer;

use BEAR\Resource\FakeRoot;
use BEAR\Resource\JsonRenderer;
use BEAR\Resource\Uri;
use PHPUnit\Framework\TestCase;

use function dirname;
use function file_get_contents;
use function ini_get;
use function ini_set;
use function log;

class JsonRendererTest extends TestCase
{
    private FakeRoot $ro;

    protected function setUp(): void
    {
        $this->ro = new FakeRoot();
        $this->ro->uri = new Uri('app://self/dummy');
        $this->ro->setRenderer(new JsonRenderer());
    }

    public function testRender(): void
    {
        $ro = $this->ro->onGet();
        $data = (string) $ro;
        $expected = '{"one":1,"two":{"tree":3}}';
        $this->assertSame($expected, $data);
    }

    public function testRenderScalar(): void
    {
        $this->ro->body = 1;
        $data = (string) $this->ro;
        $expected = '{"value":1}';
        $this->assertSame($expected, $data);
    }

    public function testError(): void
    {
        $log = ini_get('error_log');
        $logFile = dirname(__DIR__) . '/log/error.log';
        ini_set('error_log', $logFile);
        $this->ro['inf'] = log(0);
        $data = (string) $this->ro;
        $this->assertIsString($data);
        ini_set('error_log', (string) $log);
        $this->assertStringContainsString('json_encode error', (string) file_get_contents($logFile));
    }

    public function testHeader(): void
    {
        $ro = $this->ro->onGet();
        (string) $ro; // @phpstan-ignore-line
        $expected = 'application/json';
        $this->assertSame($expected, $ro->headers['Content-Type']);
    }
}
