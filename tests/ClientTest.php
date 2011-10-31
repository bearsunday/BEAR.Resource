<?php

namespace BEAR\Resource;

use Ray\Di\Config;

/**
 * Test class for PHP.Skelton.
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    protected $skelton;

    protected function setUp()
    {
        parent::setUp();
        
        $this->resource = new Client(new Config);
        $this->user = new Mock\User;
    }

    public function test_New()
    {
        $this->assertInstanceOf('\BEAR\Resource\Client', $this->resource);
    }

    public function test_NewMock()
    {
        $this->assertInstanceOf('\BEAR\Resource\Mock\User', $this->user);
    }
    
    public function testPostWithNoDefaultParameter()
    {
        $actual = $this->resource->post($this->user, array(
            'id' => 10,
            'name' => 'Ray',
            'age' => 43)
        );
        $expected = "post user[10 Ray 43]";
        $this->assertSame($expected, $actual);
    }
    
    /**
     * @expectedException BEAR\Resource\Exception\InvalidParameter
     */
    public function testPostInvalidParameter()
    {
        $actual = $this->resource->post($this->user, array(
            'id' => 10
        ));
    }
    
    public function testPutWithDefaultParameter()
    {
        $actual = $this->resource->put($this->user, array(
            'id' => 7,
            'name' => 'BEAR'
        ));
        $expected = "put user[7 BEAR 10]";
        $this->assertSame($expected, $actual);
    }
    
    /**
     * @expectedException BEAR\Resource\Exception
     */
    public function test_Exception()
    {
        throw new Exception;
    }
}