<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BadFunctionCallException;
use BEAR\Resource\Module\ResourceModule;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class HttpResourceObjectTest extends TestCase
{
    /**
     * @var ResourceInterface
     */
    private $resource;

    protected function setUp() : void
    {
        $injector = new Injector(new ResourceModule('FakeVendor\Sandbox'), __DIR__ . '/tmp');
        $this->resource = $injector->getInstance(ResourceInterface::class);
    }

    public function testGet()
    {
        $response = $this->resource->get('http://httpbin.org/get', ['foo' => 'bar']);
        $this->assertSame(200, $response->code);
        $this->assertArrayHasKey('access-control-allow-credentials', $response->headers);
        $this->assertArrayHasKey('args', $response->body);
        $this->assertContains('"args": {', $response->view);
    }

    public function testPost()
    {
        $response = $this->resource->post('http://httpbin.org/post', ['foo' => 'bar']);
        $this->assertSame(200, $response->code);
        $this->assertArrayHasKey('access-control-allow-credentials', $response->headers);
        $body = $response->body;
        $this->assertSame('bar', $body['form']['foo']);
        $this->assertContains('"form": {', $response->view);
    }

    public function testPut()
    {
        $response = $this->resource->put('http://httpbin.org/put', ['foo' => 'bar']);
        $this->assertSame(200, $response->code);
        $this->assertArrayHasKey('access-control-allow-credentials', $response->headers);
        $body = $response->body;
        $this->assertSame('bar', $body['form']['foo']);
        $this->assertContains('"form": {', $response->view);
    }

    public function testPatch()
    {
        $response = $this->resource->patch('http://httpbin.org/patch', ['foo' => 'bar']);
        $this->assertSame(200, $response->code);
        $this->assertArrayHasKey('access-control-allow-credentials', $response->headers);
        $body = $response->body;
        $this->assertSame('bar', $body['form']['foo']);
        $this->assertContains('"form": {', $response->view);
    }

    public function testDelete()
    {
        $response = $this->resource->delete('http://httpbin.org/delete', ['foo' => 'bar']);
        $this->assertSame(200, $response->code);
        $this->assertArrayHasKey('access-control-allow-credentials', $response->headers);
        $body = $response->body;
        $this->assertSame('bar', $body['form']['foo']);
        $this->assertContains('"form": {', $response->view);
    }

    public function testToString()
    {
        $response = $this->resource->get('http://httpbin.org/get', ['foo' => 'bar']);
        $actual = (string) $response;
        $this->assertContains('"args": {', $actual);
    }

    public function testIsSet()
    {
        $response = $this->resource->get('http://httpbin.org/get', ['foo' => 'bar']);
        $isSet = isset($response->__invalid);
        $this->assertFalse($isSet);
    }

    public function testSet()
    {
        $this->expectException(BadFunctionCallException::class);
        $response = $this->resource->get('http://httpbin.org/get', ['foo' => 'bar']);
        $response->foo = '1';
    }

    public function testInvalidGet()
    {
        $this->expectException(InvalidArgumentException::class);
        $response = $this->resource->get('http://httpbin.org/get', ['foo' => 'bar']);
        $response->foo;
    }
}
