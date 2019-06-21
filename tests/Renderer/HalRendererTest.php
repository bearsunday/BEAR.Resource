<?php

declare(strict_types=1);

namespace BEAR\Resource\Renderer;

use BEAR\Resource\FakeHal;
use BEAR\Resource\HalLink;
use BEAR\Resource\HalRenderer;
use BEAR\Resource\NullReverseLink;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\Uri;
use Doctrine\Common\Annotations\AnnotationReader;
use function file_get_contents;
use PHPUnit\Framework\TestCase;

class HalRendererTest extends TestCase
{
    /**
     * @var FakeHal
     */
    private $ro;

    protected function setUp() : void
    {
        $this->ro = new FakeHal;
        $this->ro->uri = new Uri('app://self/dummy');
        $this->ro->setRenderer(new HalRenderer(new AnnotationReader(), new HalLink(new NullReverseLink)));
    }

    public function testRender()
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

    public function testRenderScalar()
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

    public function testError()
    {
        $log = ini_get('error_log');
        $logFile = dirname(__DIR__) . '/log/error.log';
        ini_set('error_log', $logFile);
        $this->ro['inf'] = log(0);
        $data = (string) $this->ro;
        $this->assertIsString($data);
        ini_set('error_log', $log);
        $this->assertContains('json_encode error', (string) file_get_contents($logFile));
    }

    public function testHeader()
    {
        /* @var $ro ResourceObject */
        $ro = $this->ro->onGet();
        (string) $ro;
        $expected = 'application/hal+json';
        $this->assertSame($expected, $ro->headers['content-type']);
    }

    public function testBodyLink()
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

    public function testLocationHeader()
    {
        $ro = $this->ro->onGet();
        $ro->headers['Location'] = '/foo';
        (string) $ro;
        $this->assertSame('/foo', $ro->headers['Location']);
    }
}
