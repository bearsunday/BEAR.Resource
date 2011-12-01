<?php

namespace BEAR\Resource;

class UriTest extends \PHPUnit_Framework_TestCase
{
    protected $skelton;

    protected function setUp()
    {
        parent::setUp();
        $this->uri = new Uri('dummy://self/path/to/resource', array('id' => 1, 'name' => 'BEAR'));
    }

    public function test_offsetGet()
    {
         $this->assertTrue(is_string((string)$this->uri));
    }
}

