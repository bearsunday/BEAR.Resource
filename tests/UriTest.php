<?php

declare(strict_types=1);

namespace BEAR\Resource;

use PHPUnit\Framework\TestCase;
use Stringable;

class UriTest extends TestCase
{
    public function testUri(): void
    {
        $uri = new Uri('app://self/?id=1');
        $this->assertSame('app', $uri->scheme);
        $this->assertSame('self', $uri->host);
        $this->assertSame('/', $uri->path);
        $this->assertSame('app', $uri->scheme);
        $this->assertSame('get', $uri->method);
    }

    public function testUriWithQuery(): void
    {
        $uri = new Uri('app://self/', ['id' => 1]);
        $expect = 'app://self/?id=1';
        $this->assertSame($expect, (string) $uri);
        $this->assertSame(['id' => 1], $uri->query);
    }

    public function testUriWithMoreQuery(): void
    {
        $uri = new Uri('app://self/{?id}', ['id' => 1, 'name' => 'sunday']);
        $expect = 'app://self/?id=1&name=sunday';
        $this->assertSame($expect, (string) $uri);
        $this->assertSame(['id' => 1, 'name' => 'sunday'], $uri->query);
    }

    public function testAStringableQueryValueIsWrittenAsItsString(): void
    {
        $id = new class ('x') implements Stringable {
            public function __construct(private string $value)
            {
            }

            public function __toString(): string
            {
                return $this->value;
            }
        };
        $uri = new Uri('app://self/tag', ['id' => $id, 'filter' => ['tag' => $id]]);

        $this->assertSame('app://self/tag?id=x&filter%5Btag%5D=x', (string) $uri);
        $this->assertSame($id, $uri->query['id']);
    }
}
