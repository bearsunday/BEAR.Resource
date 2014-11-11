<?php

namespace BEAR\Resource;

use FakeVendor\Sandbox\Resource\App\User;

class ATest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->a = new A;
    }

    public function testNew()
    {
        $this->assertInstanceOf('\BEAR\Resource\A', $this->a);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\Link
     */
    public function testHrefInvalidRel()
    {
        $resource = new Mock\Entry;
        $this->a->href('not_found', $resource);
    }

    public function testHrefWithTemplateUri()
    {
        $resource = new Mock\User;
        $uri = $this->a->href('friend', $resource);
        $this->assertSame('app://self/friend/?id=1', $uri);
    }

    public function testHrefWithoutTemplateUri()
    {
        $resource = new Mock\User;
        $uri = $this->a->href('profile', $resource);
        $this->assertSame('app://self/profile', $uri);
    }
}
