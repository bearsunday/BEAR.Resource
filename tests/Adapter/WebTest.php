<?php

namespace BEAR\Resource\Adapter;

use Ray\Di\Injector;
use BEAR\Resource\SchemeCollection;
use BEAR\Resource\Factory;
use BEAR\Resource\Adapter\Web\WebClient;
use BEAR\Resource\ResourceObject;

class WebTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BEAR\Resource\Adapter\Web
     */
    protected $webAdapter;

    protected function setUp()
    {
        parent::setUp();
        $webDirs = [$_ENV['TEST_DIR'] . '/TestVendor/Web'];
        $this->webAdapter = new Web($webDirs);

    }

    public function testNew()
    {
        $this->assertInstanceOf('\BEAR\Resource\Adapter\Web', $this->webAdapter);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\ResourceNotFound
     */
    public function testGetServiceNotFound()
    {
        $this->webAdapter->get('web://invalid_service/');
    }

    /**
     * @expectedException \BEAR\Resource\Exception\WebApiOperationNotFound
     */
    public function testGetOperationNotFound()
    {
        $client = $this->webAdapter->get('web://kuma_service/invalid_operation');
        $client->onGet();
    }

    /**
     * @expectedException \BEAR\Resource\Exception\WebApiRequest
     */
    public function testGetInvalidOperationRequestPathNotFound()
    {
        $client = $this->webAdapter->get('web://kuma_service/bear_404_resource');
        $client->onGet();
    }

    public function testGetInstance()
    {
        $instance = $this->webAdapter->get('web://kuma_service/bear_resource');
        $this->assertInstanceOf('BEAR\Resource\Adapter\Web\WebClient', $instance);

        return $instance;
    }

    public function testGetInstanceGet()
    {
        $instance = $this->webAdapter->get('web://kuma_service/bear_resource');
        $response = $instance->onGet();
        $this->assertInstanceOf('\BEAR\Resource\ResourceObject', $response);

        return $response;
    }

    /**
     * @param ResourceObject $response
     *
     * @depends testGetInstanceGet
     */
    public function testGetRequestBody(ResourceObject $response)
    {
        $this->assertSame('http://httpbin.org/get', $response['url']);
    }

    public function testGetInstancePost()
    {
        $instance = $this->webAdapter->get('web://kuma_service/bear_resource');
        $response = $instance->onPost(['name' => 'koriym', 'age' => 11]);
        $this->assertInstanceOf('\BEAR\Resource\ResourceObject', $response);

        return $response;
    }

    /**
     * @param ResourceObject $response
     *
     * @depends testGetInstancePost
     */
    public function testGetInstancePostData(ResourceObject $response)
    {
        $this->assertSame('koriym', $response->body['form']['name']);
    }
}
