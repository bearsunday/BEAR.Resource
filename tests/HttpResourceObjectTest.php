<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Dev\Http\BuiltinServer;
use BEAR\Resource\Module\ResourceModule;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

use function assert;
use function is_array;

class HttpResourceObjectTest extends TestCase
{
    private const HOST = '127.0.0.1:8099';
    private const URL = 'http://127.0.0.1:8099/';
    private static BuiltinServer $server;
    private ResourceInterface $resource;

    public static function setUpBeforeClass(): void
    {
        self::$server = new BuiltinServer(self::HOST, __DIR__ . '/Server/index.php');
        self::$server->start();
    }

    protected function setUp(): void
    {
        $injector = new Injector(new ResourceModule('FakeVendor\Sandbox'), __DIR__ . '/tmp');
        $this->resource = $injector->getInstance(ResourceInterface::class);
    }

    public function testGet(): HttpResourceObject
    {
        $response = $this->resource->get(self::URL, ['foo' => 'bar']);
        $this->assertSame(200, $response->code);
        $this->assertArrayHasKey('Content-Type', $response->headers);
        assert(is_array($response->body));
        $this->assertArrayHasKey('args', $response->body);
        $this->assertStringContainsString('"args": {', (string) $response->view);
        assert($response instanceof HttpResourceObject);

        return $response;
    }

    public function testPost(): void
    {
        $response = $this->resource->post(self::URL, ['foo' => 'bar']);
        $this->assertSame(200, $response->code);
        $body = $response->body;
        $this->assertSame('bar', $body['form']['foo']); // @phpstan-ignore-line
        $this->assertStringContainsString('"form": {', (string) $response->view);
    }

    public function testPut(): void
    {
        $response = $this->resource->put(self::URL, ['foo' => 'bar']);
        $this->assertSame(200, $response->code);
        $body = $response->body;
        $this->assertSame('bar', $body['form']['foo']);  // @phpstan-ignore-line
        $this->assertStringContainsString('"form": {', (string) $response->view);
    }

    public function testPatch(): void
    {
        $response = $this->resource->patch(self::URL, ['foo' => 'bar']);
        $this->assertSame(200, $response->code);
        $body = $response->body;
        $this->assertSame('bar', $body['form']['foo']);  // @phpstan-ignore-line
        $this->assertStringContainsString('"form": {', (string) $response->view);
    }

    public function testDelete(): void
    {
        $response = $this->resource->delete(self::URL, ['foo' => 'bar']);
        $this->assertSame(200, $response->code);
        $body = $response->body;
        $this->assertSame('bar', $body['form']['foo']);  // @phpstan-ignore-line
    }

    /** @depends testGet */
    public function testToString(HttpResourceObject $response): void
    {
        $actual = (string) $response;
        $this->assertStringContainsString('"args": {', $actual);
    }

    /** @depends testGet */
    public function testIsSet(HttpResourceObject $response): void
    {
        $isSet = isset($response->invalid);
        $this->assertFalse($isSet);
    }

    /** @notest */
    public function testHtmlResponse(): void
    {
        $module = new ResourceModule('FakeVendor\Sandbox');
        $module->override(new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(HttpRequestHeaders::class)->toInstance(new HttpRequestHeaders(['x-request-header1: 1', 'Content-Type: text/html']));
            }
        });
        $injector = new Injector($module, __DIR__ . '/tmp');

        $this->resource = $injector->getInstance(ResourceInterface::class);
        self::$server = new BuiltinServer(self::HOST, __DIR__ . '/Server/index.php?media=html');
        self::$server->start();
        $response = $this->resource->get(self::URL);
        $this->assertSame(200, $response->code);
        $this->assertSame('<html></html>', (string) $response->view);
        $this->assertSame([], $response->body);
    }
}
