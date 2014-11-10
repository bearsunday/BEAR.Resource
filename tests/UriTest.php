<?php

namespace BEAR\Resource;

use BEAR\Resource\Exception\Uri as UriException;

class UriTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Uri
     */
    private $uri;

    protected function setUp()
    {
        parent::setUp();
        $this->uri = new Uri('app://self/path/to/resource?name=Sunday', ['id' => 1, 'name' => 'BEAR']);
    }

    public function testToString()
    {
        $this->assertSame('app://self/path/to/resource?id=1&name=BEAR', (string) $this->uri);
        $this->assertSame('app', $this->uri->scheme);
        $this->assertSame('self', $this->uri->host);
        $this->assertSame('/path/to/resource', $this->uri->path);
    }

    public function invalidUriProvider()
    {
        return [
            ['app://self'],
            ['/path/to/resource']
        ];
    }

    public function validUriProvider()
    {
        return [
            ['app://self/'],
            ['app://self/path/to/resource'],
            ['app://self/path/to/resource?name=BEAR']
        ];
    }


    /**
     * @dataProvider invalidUriProvider
     */
    public function testInvalidUri($uri)
    {
        $this->setExpectedException(UriException::class);
        new Uri($uri);
    }

    /**
     * @dataProvider validUriProvider
     */
    public function testValidUri($uri)
    {
        $this->assertInternalType('string', (string) $uri);
    }
}
