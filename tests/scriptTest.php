<?php

namespace BEAR\Resource;

class scriptTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->resource = require dirname(__DIR__) . '/scripts/instance.php';
    }

    public function test_New()
    {
        $this->assertInstanceOf('\BEAR\Resource\Resource', $this->resource);
    }
}
