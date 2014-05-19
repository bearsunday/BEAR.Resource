<?php

namespace BEAR\Resource;

class UriTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Uri
     */
    private $uri;

    protected function setUp()
    {
        parent::setUp();
        $this->uri = new Uri('dummy://self/path/to/resource', array('id' => 1, 'name' => 'BEAR'));
    }

    public function testOffsetGet()
    {
        $this->assertTrue(is_string((string) $this->uri));
    }
}
