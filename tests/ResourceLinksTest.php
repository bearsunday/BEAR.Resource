<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\LinkException;
use LogicException;
use PHPUnit\Framework\TestCase;

class ResourceLinksTest extends TestCase
{
    private HalLinker $linker;
    private FakeHalHref $ro;
    private ResourceLinks $link;

    protected function setUp(): void
    {
        $this->linker = new HalLinker(new NullReverseLinker());
        $this->ro = new FakeHalHref();
        $this->ro->uri = new Uri('app://self/posts');
        $this->ro->body = ['page' => 2];
        $this->link = new ResourceLinks($this->ro, $this->linker);
    }

    public function testReadsTheLinkOfARel(): void
    {
        $this->assertSame(['href' => '/posts?page=2', 'title' => 'Next page'], $this->link['next']);
        $this->assertSame($this->linker->link($this->ro, 'next'), $this->link['next']);
    }

    public function testReadsTheBodyWhenRead(): void
    {
        $this->ro->body = ['page' => 3];

        $this->assertSame('/posts?page=3', $this->link['next']['href']);
    }

    public function testEveryRelIsSet(): void
    {
        $this->assertTrue(isset($this->link['prev']));
    }

    public function testReadingAnUndeclaredRelThrows(): void
    {
        $this->expectException(LinkException::class);
        $this->expectExceptionMessage('rel:prev class:' . FakeHalHref::class);

        $this->link['prev'];
    }

    public function testCannotBeWritten(): void
    {
        $this->expectException(LogicException::class);

        $this->link['next'] = ['href' => '/elsewhere', 'title' => ''];
    }

    public function testCannotBeUnset(): void
    {
        $this->expectException(LogicException::class);

        unset($this->link['next']);
    }
}
