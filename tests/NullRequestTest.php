<?php

declare(strict_types=1);

namespace BEAR\Resource;

use PHPUnit\Framework\TestCase;

class NullRequestTest extends TestCase
{
    public function testWithQuery()
    {
        $request = (new NullRequest)->withQuery([]);
        $this->assertInstanceOf(NullRequest::class, $request);
    }

    public function testAddQuery()
    {
        $request = (new NullRequest)->addQuery([]);
        $this->assertInstanceOf(NullRequest::class, $request);
    }

    public function testlinkSelf()
    {
        $request = (new NullRequest)->linkSelf('');
        $this->assertInstanceOf(NullRequest::class, $request);
    }

    public function testlinkNew()
    {
        $request = (new NullRequest)->linkNew('');
        $this->assertInstanceOf(NullRequest::class, $request);
    }

    public function testlinkCrawl()
    {
        $request = (new NullRequest)->linkCrawl('');
        $this->assertInstanceOf(NullRequest::class, $request);
    }

    public function testToUri()
    {
        $this->assertSame('app://self/index', (new NullRequest)->toUri());
    }

    public function testToUriWithMethod()
    {
        $this->assertSame('get app://self/index', (new NullRequest)->toUriWithMethod());
    }
}
