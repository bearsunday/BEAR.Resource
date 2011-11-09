<?php

namespace BEAR\Resource;

use BEAR\Resource\Request\Method,
    BEAR\Resource\Adapter\Nop;

/**
 * Test class for PHP.Skelton.
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
    protected $request;

    protected function setUp()
    {
        parent::setUp();
        $this->request = new Request;
    }

    public function test_New()
    {
        $this->assertInstanceOf('\BEAR\Resource\Request', $this->request);
    }

    public function test___toString()
    {
        $this->request->method = 'get';
        $this->request->ro = new Nop;
        $this->request->ro->uri = 'nop://self/path/to/resource';
        $this->request->query = array('name' => 'koriym', 'age' => 25);
        $actual = (string)$this->request;
        $this->assertSame('get nop://self/path/to/resource?name=koriym&age=25', $actual);
    }
}