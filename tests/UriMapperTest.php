<?php

namespace BEAR\Resource;

class UriMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UriMapper
     */
    protected $uriMapper;

    public function setUp()
    {
        $this->uriMapper = new UriMapper;
        $this->uriMapper->setApiPath('api_base');
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Resource\UriMapper', $this->uriMapper);
    }

    public function testMap()
    {
        $uri = $this->uriMapper->map('app/blog/posts');
        $this->assertSame('app://self/blog/posts', $uri);
    }

    public function testMapWithServer()
    {
        $uri = $this->uriMapper->map('app/blog/posts');
        $this->assertSame('app://self/blog/posts', $uri);
    }

    public function testReverseMap()
    {
        $href = $this->uriMapper->reverseMap('app://blog/posts');
        $this->assertSame('/api_base/posts', $href);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\UriException
     */
    public function testReverseException()
    {
        $this->uriMapper->reverseMap('invalid_uri');
    }
}
