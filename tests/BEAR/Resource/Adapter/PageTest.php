<?php

namespace BEAR\Resource\Adapter;

use BEAR\Resource\Exception\BadRequest;
use BEAR\Resource\Mock;
use BEAR\Resource\SchemeCollection;
use Doctrine\Common\Annotations\AnnotationReader as Reader;
use Ray\Di\Definition;
use Ray\Di\Injector;

/**
 * Test class for BEAR.Resource.
 */
class PageTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $injector = Injector::create();
        $scheme = new SchemeCollection;
        $scheme->scheme('nop')->host('self')->toAdapter(new Nop);
        $scheme->scheme('prov')->host('self')->toAdapter(new Prov);
        $scheme->scheme('app')->host('self')->toAdapter(
            new App($injector, 'testworld', 'ResourceObject')
        );
        $this->resource = require dirname(dirname(dirname(dirname(__DIR__)))) . '/scripts/instance.php';
        $this->resource->setSchemeCollection($scheme);
        $this->user = $this->resource->newInstance('app://self/user');
        $this->nop = $this->resource->newInstance('nop://self/dummy');
        $this->query = array(
            'id' => 10,
            'name' => 'Ray',
            'age' => 43
        );

    }

    public function test_New()
    {
        $this->assertInstanceOf('\BEAR\Resource\Resource', $this->resource);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\BadRequest
     */
    public function test_Exception()
    {
        throw new BadRequest;
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
        $query = [];
        $request = $this->resource->get->object($this->nop)->withQuery($query)->request();
        $this->assertInstanceOf('\BEAR\Resource\Request', $request);
    }

    public function test_get()
    {
        $request = $this->resource->get->object($this->nop)->withQuery($this->query)->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function test_post()
    {
        $request = $this->resource->post->object($this->nop)->withQuery($this->query)->request();
        $expected = "post nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function test_postPoeCsrf()
    {
        $request = $this->resource->post->object($this->nop)->withQuery($this->query)->poe->csrf->request();
        $expected = "post nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    /**
     * @expectedException \BEAR\Resource\Exception\BadRequest
     */
    public function test_postInvalidOption()
    {
        $request = $this->resource->post->object($this->nop)->withQuery(
            $this->query
        )->poe->csrf->invalid_option_cause_exception->request();
        $expected = "post nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function test_put()
    {
        $request = $this->resource->put->object($this->nop)->withQuery($this->query)->request();
        $expected = "put nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function test_delete()
    {
        $request = $this->resource->delete->object($this->nop)->withQuery($this->query)->request();
        $expected = "delete nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function test_linkSelfString()
    {
        $request = $this->resource->get->object($this->nop)->withQuery($this->query)->linkSelf('dummyLink')->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function test_linkNewString()
    {
        $request = $this->resource->get->object($this->nop)->withQuery($this->query)->linkNew('dummyLink')->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function test_linkCrawlString()
    {
        $request = $this->resource->get->object($this->nop)->withQuery($this->query)->linkCrawl('dummyLink')->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function test_linkTwo()
    {
        $request = $this->resource->get->object($this->nop)->withQuery($this->query)->linkSelf('dummyLink')->linkSelf(
            'dummyLink2'
        )->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function testPostWithNoDefaultParameter()
    {
        $actual = $this->resource->post->object($this->user)->withQuery($this->query)->eager->request();
        $expected = "post user[10 Ray 43]";
        $this->assertSame($expected, $actual->body);
    }

    public function test_uri()
    {
        $request = $this->resource->get->uri('nop://self/dummy')->withQuery($this->query)->request();
        $expected = "get nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, $request->toUriWithMethod());
    }

    public function test_clientString()
    {
        $client = $this->resource->get->uri('nop://self/dummy')->withQuery($this->query);
        $expected = "nop://self/dummy?id=10&name=Ray&age=43";
        $this->assertSame($expected, (string)$client);
    }

    public function testPutWithDefaultParameter()
    {
        $actual = $this->resource->post->object($this->user)->withQuery(array('id' => 1))->eager->request();
        $expected = "post user[1 default_name 99]";
        $this->assertSame($expected, $actual->body);
    }

    public function testP()
    {
        $ro = new Mock\Link;
        $actual = $this->resource->get->object($ro)->withQuery(array('id' => 1))->linkSelf('View')->eager->request();
        $expected = '<html>bear1</html>';
        $this->assertSame($expected, $actual->body);
    }

}
