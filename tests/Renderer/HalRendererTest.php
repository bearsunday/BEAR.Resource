<?php

declare(strict_types=1);

namespace BEAR\Resource\Renderer;

use BEAR\Resource\FakeHal;
use BEAR\Resource\HalLink;
use BEAR\Resource\HalRenderer;
use BEAR\Resource\NullReverseLink;
use BEAR\Resource\Uri;
use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;

class HalRendererTest extends TestCase
{
    /** @var FakeHal */
    private $ro;

    protected function setUp(): void
    {
        $this->ro = new FakeHal();
        $this->ro->uri = new Uri('app://self/dummy');
        $this->ro->setRenderer(new HalRenderer(new AnnotationReader(), new HalLink(new NullReverseLink())));
    }

    public function testRender(): void
    {
        $ro = $this->ro->onGet();
        $data = (string) $ro;
        $expected = <<<'EOT'
{
    "one": 1,
    "_embedded": {
        "two": {
            "tree": 3,
            "_links": {
                "self": {
                    "href": "/bear/resource/fakechild"
                }
            }
        }
    },
    "_links": {
        "self": {
            "href": "/dummy"
        },
        "profile": {
            "href": "/profile"
        }
    }
}

EOT;
        $this->assertSame($expected, $data);
    }

    public function testRenderScalar(): void
    {
        $this->ro->body = 1;
        $data = (string) $this->ro;
        $expected = <<<'EOT'
{
    "value": 1,
    "_links": {
        "self": {
            "href": "/dummy"
        },
        "profile": {
            "href": "/profile"
        }
    }
}

EOT;
        $this->assertSame($expected, $data);
    }

    public function testHeader(): void
    {
        $ro = $this->ro->onGet();
        (string) $ro; // @phpstan-ignore-line
        $expected = 'application/hal+json';
        $this->assertSame($expected, $ro->headers['Content-Type']);
    }

    public function testBodyLink(): void
    {
        $ro = $this->ro->onGet(true);
        $actual = (string) $ro;
        $expected = <<<'EOT'
{
    "one": 1,
    "_links": {
        "self": {
            "href": "/dummy"
        },
        "profile": {
            "href": "/changed-profile"
        }
    },
    "_embedded": {
        "two": {
            "tree": 3,
            "_links": {
                "self": {
                    "href": "/bear/resource/fakechild"
                }
            }
        }
    }
}

EOT;
        $this->assertSame($expected, $actual);
    }

    public function testLocationHeader(): void
    {
        $ro = $this->ro->onGet();
        $ro->headers['Location'] = '/foo';
        (string) $ro; // @phpstan-ignore-line
        $this->assertSame('/foo', $ro->headers['Location']);
    }

    public function testNonArrayBody(): void
    {
        $ro = $this->ro->onGet();
        $ro->body = '1';
        $actual = (string) $ro;
        $expected = <<<'EOT'
{
    "value": "1",
    "_links": {
        "self": {
            "href": "/dummy"
        },
        "profile": {
            "href": "/profile"
        }
    }
}

EOT;
        $this->assertSame($expected, $actual);
    }
}
