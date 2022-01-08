<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BadFunctionCallException;
use BEAR\Resource\Module\ResourceModule;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function assert;
use function is_array;

class HttpResourceObjectTest extends TestCase
{
    /** @var ResourceInterface */
    private $resource;

    protected function setUp(): void
    {
        $injector = new Injector(new ResourceModule('FakeVendor\Sandbox'), __DIR__ . '/tmp');
        $this->resource = $injector->getInstance(ResourceInterface::class); // @phpstan-ignore-linel
    }

    public function testGet(): HttpResourceObject
    {
        $response = $this->resource->get('http://httpbin.org/get', ['foo' => 'bar']);
        $this->assertSame(200, $response->code);
        $this->assertArrayHasKey('Access-control-allow-credentials', $response->headers);
        assert(is_array($response->body));
        $this->assertArrayHasKey('args', $response->body);
        $this->assertStringContainsString('"args": {', (string) $response->view);
        assert($response instanceof HttpResourceObject);

        return $response;
    }

    public function testPost(): void
    {
        $response = $this->resource->post('http://httpbin.org/post', ['foo' => 'bar']);
        $this->assertSame(200, $response->code);
        $this->assertArrayHasKey('Access-control-allow-credentials', $response->headers);
        $body = $response->body;
        $this->assertSame('bar', $body['form']['foo']); // @phpstan-ignore-line
        $this->assertStringContainsString('"form": {', (string) $response->view);
    }

    public function testPut(): void
    {
        $response = $this->resource->put('http://httpbin.org/put', ['foo' => 'bar']);
        $this->assertSame(200, $response->code);
        $this->assertArrayHasKey('Access-control-allow-credentials', $response->headers);
        $body = $response->body;
        $this->assertSame('bar', $body['form']['foo']);  // @phpstan-ignore-line
        $this->assertStringContainsString('"form": {', (string) $response->view);
    }

    public function testPatch(): void
    {
        $response = $this->resource->patch('http://httpbin.org/patch', ['foo' => 'bar']);
        $this->assertSame(200, $response->code);
        $this->assertArrayHasKey('Access-control-allow-credentials', $response->headers);
        $body = $response->body;
        $this->assertSame('bar', $body['form']['foo']);  // @phpstan-ignore-line
        $this->assertStringContainsString('"form": {', (string) $response->view);
    }

    public function testDelete(): void
    {
        $response = $this->resource->delete('http://httpbin.org/delete', ['foo' => 'bar']);
        $this->assertSame(200, $response->code);
        $this->assertArrayHasKey('Access-control-allow-credentials', $response->headers);
        $body = $response->body;
        $this->assertSame('bar', $body['form']['foo']);  // @phpstan-ignore-line
        $this->assertStringContainsString('"form": {', (string) $response->view);
    }

    /**
     * @depends testGet
     */
    public function testToString(HttpResourceObject $response): void
    {
        $actual = (string) $response;
        $this->assertStringContainsString('"args": {', $actual);
    }

    /**
     * @depends testGet
     */
    public function testIsSet(HttpResourceObject $response): void
    {
        $isSet = isset($response->invalid);
        $this->assertFalse($isSet);
    }

    /**
     * @depends testGet
     */
    public function testSet(HttpResourceObject $response): void
    {
        $this->expectException(BadFunctionCallException::class);
        $response->foo = '1'; // @phpstan-ignore-line
    }

    /**
     * @depends testGet
     */
    public function testInvalidGet(HttpResourceObject $response): void
    {
        $this->expectException(InvalidArgumentException::class);
        $response->foo; // @phpstan-ignore-line
    }
}
