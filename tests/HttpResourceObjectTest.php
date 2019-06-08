<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Module\ResourceModule;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;
use Symfony\Component\HttpClient\HttpClient;

class HttpResourceObjectTest extends TestCase
{
    /**
     * @var HttpResourceObject
     */
    private $ro;

    /**
     * @var ResourceInterface
     */
    private $resource;

    protected function setUp() : void
    {
        $client = HttpClient::create();
        $this->ro = new HttpResourceObject($client);
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
}
