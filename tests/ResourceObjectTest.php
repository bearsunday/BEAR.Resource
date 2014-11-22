<?php

namespace BEAR\Resource;

class ResourceObjectTest extends \PHPUnit_Framework_TestCase
{
    public function testTransfer()
    {
        $resourceObject = new FakeResource;
        $responder = new FakeResponder;
        $resourceObject->transfer($responder);
        $this->assertSame(FakeResource::class, $responder->class);
    }
}
