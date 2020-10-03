<?php

declare(strict_types=1);

namespace BEAR\Resource;

use PHPUnit\Framework\TestCase;

class NullRequestTest extends TestCase
{
    public function testWithQuery(): void
    {
        $request = (new NullRequest())->withQuery([]);
        $this->assertInstanceOf(NullRequest::class, $request);
    }

    public function testAddQuery(): void
    {
        $request = (new NullRequest())->addQuery([]);
        $this->assertInstanceOf(NullRequest::class, $request);
    }

    public function testlinkSelf(): void
    {
        $request = (new NullRequest())->linkSelf('');
        $this->assertInstanceOf(NullRequest::class, $request);
    }

    public function testlinkNew(): void
    {
        $request = (new NullRequest())->linkNew('');
        $this->assertInstanceOf(NullRequest::class, $request);
    }

    public function testlinkCrawl(): void
    {
        $request = (new NullRequest())->linkCrawl('');
        $this->assertInstanceOf(NullRequest::class, $request);
    }

    public function testToUri(): void
    {
        $this->assertSame('app://self/index', (new NullRequest())->toUri());
    }

    public function testToUriWithMethod(): void
    {
        $this->assertSame('get app://self/index', (new NullRequest())->toUriWithMethod());
    }

    public function testHash(): void
    {
        $this->assertSame('', (new NullRequest())->hash());
    }

    public function testRequest(): void
    {
        $this->assertInstanceOf(NullResourceObject::class, (new NullRequest())->request());
    }
}
