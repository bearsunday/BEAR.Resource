<?php

namespace BEAR\Resource;

class ArgsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Args
     */
    private $args;

    protected function setUp()
    {
        parent::setUp();
        $this->args = new Args;
    }

    public function testNew()
    {
        $this->assertInstanceOf('\BEAR\Resource\Args', $this->args);
    }
}
