<?php

namespace BEAR\Resource;

class ArgsTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->args = new Args;
    }

    public function test_New()
    {
        $this->assertInstanceOf('\BEAR\Resource\Args', $this->args);
    }
}
