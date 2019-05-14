<?php

declare(strict_types=1);

namespace BEAR\Resource;

use FakeVendor\Sandbox\Resource\App\Author;
use PHPUnit\Framework\TestCase;

class ResourceObjectTest extends TestCase
{
    public function testTransfer()
    {
        $ro = new FakeResource;
        $responder = new FakeResponder;
        $ro->transfer($responder, []);
        $this->assertSame(FakeResource::class, $responder->class);
    }

    public function testSerialize()
    {
        $ro = new FakeFreeze;
        $ro->uri = new Uri('app://self/freeze');
        $body = $ro->body;
        $serialized = serialize($ro);
        $this->assertInternalType('string', $serialized);
        $ro = unserialize($serialized);
        $this->assertInstanceOf(Author::class, $ro['user']);
        $expected = 'app://self/freeze';
        $this->assertSame($expected, (string) $ro->uri);
    }

    public function testJson()
    {
        $ro = new FakeFreeze;
        $ro->uri = new Uri('app://self/freeze');
        $json = json_encode($ro);
        $this->assertInternalType('string', $json);
        $expected = '{"php":"7","user":{"name":"Aramis","age":16,"blog_id":12}}';
        $this->assertSame($expected, $json);
    }
}
