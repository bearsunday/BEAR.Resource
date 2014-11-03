<?php

namespace BEAR\Resource\Adapter;

use Ray\Di\Injector;
use BEAR\Resource\SchemeCollection;
use BEAR\Resource\Factory;

class HttpTest extends \PHPUnit_Framework_TestCase
{
    const TEST_SERVER = 'http://www.kumasystem.com/';

    /**
     * @var Http\Guzzle
     */
    protected $httpAdapter;

    protected function setUp()
    {
        parent::setUp();
        $this->injector = new Injector;
        $scheme = new SchemeCollection;
        $scheme->scheme('http')->host('*')->toAdapter(new Http);
        $this->factory = new Factory($scheme);
        $this->httpAdapter = $this->factory->newInstance(
            'http://www.feedforall.com/sample.xml'
        );
    }

    public function testNew()
    {
        $this->assertInstanceOf('\BEAR\Resource\Adapter\Http\Guzzle', $this->httpAdapter);
    }

    public function testGetHeader()
    {
        $ro = $this->httpAdapter->onGet();
        $this->assertSame($ro->headers['content-type'][0], 'application/xml');
    }

    public function testGetHeaderRepeatWithCache()
    {
        foreach (range(1, 10) as $i) {
            $ro = $this->httpAdapter->onGet();
        }
        /** @noinspection PhpUndefinedVariableInspection */
        $this->assertSame($ro->headers['content-type'][0], 'application/xml');
    }

    public function testGetBody()
    {
        $ro = $this->httpAdapter->onGet();
        $actual = (string) ($ro->body->channel->title[0]);
        $expected = 'FeedForAll Sample Feed';
        $this->assertSame($expected, $actual);
    }

    public function testHead()
    {
        $ro = $this->httpAdapter->onHead();
        $expected = 'application/xml';
        $this->assertSame($expected, $ro->headers['content-type'][0]);
    }

    public function testPut()
    {
        $httpAdapter = $this->factory->newInstance(self::TEST_SERVER . '/fixture/server.php');
        /* @var $httpAdapter |BEAR\Resource\Adapter\Http\Guzzle */
        $ro = $httpAdapter->onPut();
        $method = $ro->body->REQUEST_METHOD;
        $this->assertSame('PUT', $method);
    }

    public function testDelete()
    {
        $httpAdapter = $this->factory->newInstance(self::TEST_SERVER . '/fixture/server.php');
        $ro = $httpAdapter->onDelete();
        $method = $ro->body->REQUEST_METHOD;
        $this->assertSame('DELETE', $method);
    }

    public function testOptions()
    {
        $httpAdapter = $this->factory->newInstance(self::TEST_SERVER . '/fixture/server.php');
        $ro = $httpAdapter->onOptions();
        $method = $ro->body->REQUEST_METHOD;
        $this->assertSame('OPTIONS', $method);
    }

    public function testPost()
    {
        $ro = $this->httpAdapter->onPost();
        $expected = 'application/xml';
        $this->assertSame($expected, $ro->headers['content-type'][0]);
    }

    /**
     * @expectedException \GuzzleHttp\Exception\ClientException
     */
    public function test404()
    {
        $this->httpAdapter = $this->factory->newInstance('http://www.kumasystem.com/not_exists/');
        $this->httpAdapter->onGet();
    }

    public function testJson()
    {
        $this->httpAdapter = $this->factory->newInstance('http://www.bear-project.net/test/json.php');
        $ro = $this->httpAdapter->onGet();
        $this->assertInstanceOf('BEAR\Resource\Adapter\Http\Guzzle', $ro);
    }
}
