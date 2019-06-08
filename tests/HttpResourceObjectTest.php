<?php

declare(strict_types=1);

namespace BEAR\Resource;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

class HttpResourceObjectTest extends TestCase
{
    /**
     * @var HttpResourceObject
     */
    private $ro;

    protected function setUp() : void
    {
        $client = HttpClient::create();
        $this->ro = new HttpResourceObject($client);
    }

    public function testGet()
    {
        $response = $this->ro->get('http://httpbin.org/get', ['foo' => 'bar']);
        $this->assertSame(200, $response->code);
        $this->assertArrayHasKey('access-control-allow-credentials', $response->headers);
        $this->assertArrayHasKey('args', $response->body);
        $this->assertContains('"args": {', $response->view);
        $view = $response->view;
    }

    public function testPost()
    {
        $response = $this->ro->post('http://httpbin.org/post', ['foo' => 'bar']);
        $this->assertSame(200, $response->code);
        $this->assertArrayHasKey('access-control-allow-credentials', $response->headers);
        $body = $response->body;
        $this->assertSame('bar', $body['form']['foo']);
        $this->assertContains('"form": {', $response->view);
    }

    public function testPut()
    {
        $response = $this->ro->put('http://httpbin.org/put', ['foo' => 'bar']);
        $this->assertSame(200, $response->code);
        $this->assertArrayHasKey('access-control-allow-credentials', $response->headers);
        $body = $response->body;
        $this->assertSame('bar', $body['form']['foo']);
        $this->assertContains('"form": {', $response->view);
    }

    public function testPatch()
    {
        $response = $this->ro->patch('http://httpbin.org/patch', ['foo' => 'bar']);
        $this->assertSame(200, $response->code);
        $this->assertArrayHasKey('access-control-allow-credentials', $response->headers);
        $body = $response->body;
        $this->assertSame('bar', $body['form']['foo']);
        $this->assertContains('"form": {', $response->view);
    }

    public function testDelete()
    {
        $response = $this->ro->delete('http://httpbin.org/delete', ['foo' => 'bar']);
        $this->assertSame(200, $response->code);
        $this->assertArrayHasKey('access-control-allow-credentials', $response->headers);
        $body = $response->body;
        $this->assertSame('bar', $body['form']['foo']);
        $this->assertContains('"form": {', $response->view);
    }
}
