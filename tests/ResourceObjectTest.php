<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\IlligalAccessException;
use FakeVendor\Sandbox\Resource\App\Author;
use PHPUnit\Framework\TestCase;

use function assert;
use function count;
use function is_array;
use function json_encode;
use function serialize;
use function unserialize;

use const JSON_THROW_ON_ERROR;

class ResourceObjectTest extends TestCase
{
    public function testTransfer(): void
    {
        $ro = new FakeResource();
        $responder = new FakeResponder();
        $ro->transfer($responder, []);
        $this->assertSame(FakeResource::class, $responder->class);
    }

    public function testSerialize(): void
    {
        $ro = new FakeFreeze();
        $ro->uri = new Uri('app://self/freeze');
        $body = $ro->body;
        $serialized = serialize($ro);
        $this->assertIsString($serialized);
        $ro = unserialize($serialized);
        assert($ro instanceof ResourceObject);
        $this->assertInstanceOf(Author::class, $ro['user']);
        $expected = 'app://self/freeze';
        $this->assertSame($expected, (string) $ro->uri);
    }

    public function testJson(): void
    {
        $ro = new FakeFreeze();
        $ro->uri = new Uri('app://self/freeze');
        $json = json_encode($ro, JSON_THROW_ON_ERROR);
        $this->assertIsString($json);
        $expected = '{"php":"7","user":{"name":"Aramis","age":16,"blog_id":12}}';
        $this->assertSame($expected, $json);
    }

    /** @covers \BEAR\Resource\ResourceObject::toString() */
    public function testViewCached(): void
    {
        $ro = new FakeResource();
        $view = '1';
        $ro->view = $view;
        $ro->body = ['key' => 'val'];
        $this->assertSame($view, $ro->toString());
    }

    /** @covers \BEAR\Resource\ResourceObject::count() */
    public function testIlligalAccessExceptionInCount(): void
    {
        $this->expectException(IlligalAccessException::class);
        $ro = new FakeResource();
        $ro->body = '1';
        count($ro); // @phpstan-ignore-line
    }

    public function testEvaluationInSleep(): void
    {
        $ro = new FakeResource();
        assert(is_array($ro->body));
        $ro->body['req'] = new NullRequest();
        $wakeup = unserialize(serialize($ro));
        $this->assertSame(null, $wakeup->body['req']->body); // @phpstan-ignore-line
    }

    /** @covers \BEAR\Resource\ResourceObject::offsetExists() */
    public function testIlligalAccessExceptionInOffsetExists(): void
    {
        $ro = new FakeResource();
        $ro->body = '1';
        $this->assertFalse(isset($ro['key']));
    }

    /** @covers \BEAR\Resource\ResourceObject::offsetGet() */
    public function testIlligalAccessExceptionInOffsetGet(): void
    {
        $this->expectException(IlligalAccessException::class);
        $ro = new FakeResource();
        $ro->body = '1';
        $ro['key']; // @phpstan-ignore-line
    }

    public function testClone(): void
    {
        $ro = new FakeResource();
        $ro->uri = new Uri('app://self/?modified=0');
        $this->assertSame(['modified' => '0'], $ro->uri->query);
        $clonedRo = clone $ro;
        // change cloned uri
        $clonedRo->uri->query = ['modified' => '1'];
        $this->assertSame(['modified' => '0'], $ro->uri->query);
    }

    public function testSetOffset(): void
    {
        $ro = new FakeResource();
        $ro->body = 1;
        $this->expectException(IlligalAccessException::class);
        $ro['a'] = 'a';
    }
}
