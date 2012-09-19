<?php

namespace BEAR\Resource;

/**
 * Test class for BEAR.Resource.
 */
use Guzzle\Parser\UriTemplate\UriTemplate;
use testworld\ResourceObject\User;

class ATest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->a = new A(new UriTemplate);
    }

    public function test_New()
    {
        $this->assertInstanceOf('\BEAR\Resource\A', $this->a);
    }

    /**
     * @expectedException BEAR\Resource\Exception\InvalidLink
     */
    public function test_hrefInvalidRel()
    {
        $resource = new Mock\Entry;
        $this->a->href('not_found', $resource);
    }

    public function test_hrefWithTemplateUri()
    {
        $resource = new \BEAR\Resource\Mock\User;
        $uri = $this->a->href('friend', $resource);
        $this->assertSame('app://self/friend/?id=1', $uri);
    }

    public function test_hrefWithoutTemplateUri()
    {
        $resource = new \BEAR\Resource\Mock\User;
        $uri = $this->a->href('profile', $resource);
        $this->assertSame('app://self/prfofile', $uri);
    }
}
