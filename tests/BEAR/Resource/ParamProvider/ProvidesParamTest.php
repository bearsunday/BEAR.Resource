<?php

namespace BEAR\Resource\ParamProvider;

class ProvidesParamTest extends \PHPUnit_Framework_TestCase
{
    private $providePram;

    protected function setUp()
    {
        parent::setUp();
        $this->resource = clone $GLOBALS['resource'];
        $this->providePram = new OnProvidesParam;
    }

    public function testNew()
    {
        $this->assertInstanceOf('\BEAR\Resource\ParamProvider\OnProvidesParam', $this->providePram);
    }
}
