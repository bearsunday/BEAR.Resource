<?php

namespace BEAR\Resource;

use Ray\Di\Definition;

use Ray\Di\Annotation,
Ray\Di\Config,
Ray\Di\Forge,
Ray\Di\Container,
Ray\Di\Manager,
Ray\Di\Injector,
Ray\Di\EmptyModule;

use BEAR\Resource\Builder,
BEAR\Resource\Mock\User;

use Guzzle\Service\Client as GuzzleClient;

/**
 * Test class for BEAR.Resource.
 */
class HttpTest extends \PHPUnit_Framework_TestCase
{
    protected $skelton;

    protected function setUp()
    {
        parent::setUp();
        $injector = new Injector(new Container(new Forge(new Config(new Annotation(new Definition)))), new EmptyModule);
        $scheme = new SchemeCollection;
        $scheme->scheme('http')->host('*')->toAdapter(new \BEAR\Resource\Adapter\Http);
        $this->factory = new Factory($scheme);
        $this->httpAdapter = $this->factory->newInstance('http://news.google.com/news?hl=ja&ned=us&ie=UTF-8&oe=UTF-8&output=rss');
    }

    public function test_New()
    {
        $this->assertInstanceOf('\BEAR\Resource\Adapter\Http\Guzzle', $this->httpAdapter);
    }

    public function testGetHeader()
    {
        $ro = $this->httpAdapter->onGet();
//         var_dump($ro->headers['Content-Type']);
        $this->assertSame($ro->headers['Content-Type'][0], 'application/xml; charset=UTF-8');
    }

    public function testGetHeaderRepeatWithCache()
    {
        foreach(range(1,10) as $i) {
            $ro = $this->httpAdapter->onGet();
        }
        $this->assertSame($ro->headers['Content-Type'][0], 'application/xml; charset=UTF-8');
    }

    /**
     * @covers BEAR\Resource\Adapter\Http\Guzzle
     */
    public function testGetBody()
    {
        $ro = $this->httpAdapter->onGet();
        $actual = (string)($ro->body->channel->title[0]);
        $expected = 'Top Stories - Google News';
        $this->assertSame($expected, $actual);
    }

    /**
     */
    public function testHead()
    {
        $ro = $this->httpAdapter->onHead();
        $expected = 'application/xml; charset=UTF-8';
        $this->assertSame($expected, $ro->headers['Content-Type'][0]);
    }

    /**
     *
     * @expectedException Guzzle\Http\Exception\ClientErrorResponseException
     */
    public function test404()
    {
        $this->httpAdapter = $this->factory->newInstance('http://news.google.com/notexists/');
        $ro = $this->httpAdapter->onGet();
        $expected = 'Top Stories - Google News';
        $this->assertSame($expected, $actual);
    }

    public function testJson()
    {
        $this->httpAdapter = $this->factory->newInstance('http://www.bear-project.net/test/json.php');
        $ro = $this->httpAdapter->onGet();
        $this->assertInstanceOf('BEAR\Resource\Adapter\Http\Guzzle', $ro);
    }
}
