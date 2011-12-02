<?php

namespace BEAR\Resource;

use Ray\Di\Annotation,
Ray\Di\Config,
Ray\Di\Forge,
Ray\Di\Container,
Ray\Di\Manager,
Ray\Di\Injector,
Ray\Di\EmptyModule,
BEAR\Resource\Builder,
BEAR\Resource\Mock\User;

/**
 * Test class for BEAR.Resource.
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    protected $skelton;

    protected function setUp()
    {
        parent::setUp();
        $schemeAdapters = array('nop' => '\BEAR\Resource\Adapter\Nop',
                                'prov' => '\BEAR\Resource\Mock\Prov'
        );
        $injector = new Injector(new Container(new Forge(new Config(new Annotation))), new EmptyModule);
        $namespace = array('self' => 'testworld');
        $resourceAdapters = array(
                'app' => new \BEAR\Resource\Adapter\App($injector, $namespace),
                'page' => new \BEAR\Resource\Adapter\Page($injector, $namespace),
                'nop' => new \BEAR\Resource\Adapter\Nop,
                'prov' => new \BEAR\Resource\Adapter\Prov,
                'provc' => function() {
                return new \BEAR\Resource\Adapter\Prov;
        }
        );
        $factory = new Factory($injector, $resourceAdapters);
        $invoker = new Invoker(new Config, new Linker);
        $this->resource = new Client($factory, $invoker, new Request($invoker));
        $this->user = $factory->newInstance('app://self/user');
        $this->nop = $factory->newInstance('nop://self/dummy');
        $this->query = array(
            'id' => 10,
            'name' => 'Ray',
            'age' => 43
        );

    }

    public function test_New()
    {
        $this->assertInstanceOf('\BEAR\Resource\Client', $this->resource);
    }

    /**
     * @expectedException BEAR\Resource\Exception
     */
    public function test_Exception()
    {
        throw new Exception;
    }

    public function test_newInstanceNop()
    {
        $instance = $this->resource->newInstance('nop://self/path/to/dummy');
        $this->assertInstanceOf('\BEAR\Resource\Adapter\Nop', $instance);
    }

    public function test_newInstanceAppWithProvider()
    {
        $instance = $this->resource->newInstance('prov://self/path/to/dummy');
        $this->assertInstanceOf('\stdClass', $instance);
    }


    public function testGetRequestByPost()
    {
        $query = array();
        $request = $this->resource->get->object($this->nop)->withQuery($query)->request();
        $this->assertInstanceOf('\BEAR\Resource\Request', $request);
    }

    public function test_get()
    {
        $request = $this->resource->get->object($this->nop)->withQuery($this->query)->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, (string)$request);
    }

    public function test_post()
    {
        $request = $this->resource->post->object($this->nop)->withQuery($this->query)->request();
        $expected = "post nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, (string)$request);
    }

    public function test_postPoeCsrf()
    {
        $request = $this->resource->post->object($this->nop)->withQuery($this->query)->poe->csrf->request();
        $expected = "post nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, (string)$request);
    }

    /**
     * @expectedException BEAR\Resource\Exception\BadRequest
     */
    public function test_postInvalidOption()
    {
        $request = $this->resource->post->object($this->nop)->withQuery($this->query)->poe->csrf->invalid_option_cause_exception->request();
        $expected = "post nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, (string)$request);
    }

    public function test_put()
    {
        $request = $this->resource->put->object($this->nop)->withQuery($this->query)->request();
        $expected = "put nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, (string)$request);
    }

    public function test_delete()
    {
        $request = $this->resource->delete->object($this->nop)->withQuery($this->query)->request();
        $expected = "delete nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, (string)$request);
    }

    public function test_linkSelfString()
    {
        $request = $this->resource->get->object($this->nop)->withQuery($this->query)->linkSelf('dummyLink')->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43, link self:dummyLink";
        $this->assertSame($expected, (string)$request);
    }

    public function test_linkNewString()
    {
        $request = $this->resource->get->object($this->nop)->withQuery($this->query)->linkNew('dummyLink')->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43, link new:dummyLink";
        $this->assertSame($expected, (string)$request);
    }

    public function test_linkCrawlString()
    {
        $request = $this->resource->get->object($this->nop)->withQuery($this->query)->linkCrawl('dummyLink')->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43, link crawl:dummyLink";
        $this->assertSame($expected, (string)$request);
    }

    public function test_linkTwo()
    {
        $request = $this->resource->get->object($this->nop)->withQuery($this->query)->linkSelf('dummyLink')->linkSelf('dummyLink2')->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43, link self:dummyLink, link self:dummyLink2";
        $this->assertSame($expected, (string)$request);
    }


    public function testPostWithNoDefaultParameter()
    {
        $actual = $this->resource->post->object($this->user)->withQuery($this->query)->eager->request();
        $expected = "post user[10 Ray 43]";
        $this->assertSame($expected, $actual);
    }

    public function test_uri()
    {
        $request = $this->resource->get->uri('nop://self/dummy')->withQuery($this->query)->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, (string)$request);
    }

    public function test_eager()
    {
        $request = $this->resource->get->uri('nop://self/dummy')->withQuery($this->query);
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, (string)$request);
    }


    public function testPutWithDefaultParameter()
    {
        $actual = $this->resource->post->object($this->user)->withQuery(array('id' => 1))->eager->request();
        $expected = "post user[1 default_name 99]";
        $this->assertSame($expected, $actual);
    }

    public function testP()
    {
        $ro = new Mock\Link;
        $actual = $this->resource->get->object($ro)->withQuery(array('id' => 1))->linkSelf('View')->eager->request();
        $expected = '<html>bear1</html>';
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException BEAR\Resource\Exception\InvalidUri
     */
    public function testInvalidUri()
    {
        $query = array();
        $request = $this->resource->get->uri($this->nop)->withQuery($query)->request();
        $this->assertInstanceOf('\BEAR\Resource\Request', $request);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testServiceError()
    {
        $query = array();
        $request = $this->resource->post->uri('app://self/blog')->eager->request();
    }
}