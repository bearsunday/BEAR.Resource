<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;
use PHPUnit\Framework\TestCase;

class HalLinkerTest extends TestCase
{
    public function testExpandHrefPassesTheExpandedQueryToTheReverseLinker(): void
    {
        $reverseLinker = new FakeQueryRecordingReverseLinker();
        $linker = new HalLinker($reverseLinker);

        $href = $linker->expandHref(new Link(rel: 'blog', href: '/blog{?id,tag}'), ['id' => 1, 'tag' => 'php']);

        $this->assertSame('/blog?id=1&tag=php', $href);
        $this->assertSame([['id' => '1', 'tag' => 'php']], $reverseLinker->queries);
    }

    public function testExpandHrefWithoutQueryPassesAnEmptyQuery(): void
    {
        $reverseLinker = new FakeQueryRecordingReverseLinker();
        $linker = new HalLinker($reverseLinker);

        $href = $linker->expandHref(new Link(rel: 'blog', href: '/blog/{id}'), ['id' => 1]);

        $this->assertSame('/blog/1', $href);
        $this->assertSame([[]], $reverseLinker->queries);
    }
}
