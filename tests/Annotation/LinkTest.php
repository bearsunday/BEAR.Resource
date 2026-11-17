<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use BEAR\Resource\Exception\LinkHrefException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LinkTest extends TestCase
{
    /** @return array<string, array{string}> */
    public static function fragmentProvider(): array
    {
        return [
            'a fragment' => ['app://self/author?id=1#top'],
            'a link' => ['app://self/author{?id}#link=self:blog'],
            'an empty fragment' => ['app://self/author#'],
            'the fragment operator' => ['app://self/author{?id}{#section}'],
        ];
    }

    #[DataProvider('fragmentProvider')]
    public function testAnHrefDeclaresNoFragment(string $href): void
    {
        $this->expectException(LinkHrefException::class);
        $this->expectExceptionMessage($href);

        new Link(rel: 'author', href: $href);
    }

    /** @return array<string, array{string}> */
    public static function noFragmentProvider(): array
    {
        return [
            'a variable named with #' => ['app://self/author{?a#b}'],
            'the reserved operator' => ['app://self/author{+path}'],
        ];
    }

    #[DataProvider('noFragmentProvider')]
    public function testAnHrefWithoutAFragment(string $href): void
    {
        $this->assertSame($href, (new Link(rel: 'author', href: $href))->href);
    }
}
