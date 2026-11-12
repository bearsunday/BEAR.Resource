<?php

declare(strict_types=1);

namespace BEAR\Resource\Renderer;

use BEAR\Resource\AbstractRequest;
use BEAR\Resource\FakeChild;
use BEAR\Resource\FakeHal;
use BEAR\Resource\FakeHalLinkAttr;
use BEAR\Resource\FakeLazyRequest;
use BEAR\Resource\FakeRo;
use BEAR\Resource\HalLinker;
use BEAR\Resource\HalRenderer;
use BEAR\Resource\InvokerFactory;
use BEAR\Resource\InvokerInterface;
use BEAR\Resource\NullReverseLinker;
use BEAR\Resource\Request;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\Uri;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

use function array_keys;
use function json_decode;

use const JSON_THROW_ON_ERROR;

class HalRendererTest extends TestCase
{
    private FakeHal $ro;

    protected function setUp(): void
    {
        $this->ro = new FakeHal();
        $this->ro->uri = new Uri('app://self/dummy');
        $this->ro->setRenderer(new HalRenderer(new HalLinker(new NullReverseLinker())));
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
        $this->assertJsonStringEqualsJsonString($expected, $data);
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
        $this->assertJsonStringEqualsJsonString($expected, $data);
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
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testLocationHeader(): void
    {
        $ro = $this->ro->onGet();
        $ro->headers['Location'] = '/foo';
        (string) $ro; // @phpstan-ignore-line
        $this->assertSame('/foo', $ro->headers['Location']);
    }

    public function testLocationHeaderWithQuery(): void
    {
        $ro = $this->ro->onGet();
        $ro->headers['Location'] = '/foo?id=1';
        (string) $ro; // @phpstan-ignore-line
        $this->assertSame('/foo?id=1', $ro->headers['Location']);
    }

    public function testRenderResourceWithoutMethod(): void
    {
        $ro = new class extends ResourceObject {
        };
        $ro->uri = new Uri('app://self/no-method');
        $ro->uri->method = 'post';
        $ro->body = [];
        $ro->setRenderer(new HalRenderer(new HalLinker(new NullReverseLinker())));

        $this->assertStringContainsString('"self"', (string) $ro);
    }

    public function testEmbeddedRequestWithExistingEmbeddedArray(): void
    {
        $ro = $this->resourceWithEmbeddedBody(['kept' => 1]);

        (string) $ro;

        $this->assertSame(['kept', 'two'], array_keys(self::embedded($ro)));
    }

    public function testEmbeddedRequestWithInvalidEmbeddedValue(): void
    {
        $ro = $this->resourceWithEmbeddedBody('invalid');

        (string) $ro;

        $this->assertArrayHasKey('two', self::embedded($ro));
    }

    public function testARequestUnderTheEmbeddedKeyIsEmbeddedBesideTheOthers(): void
    {
        $ro = $this->resourceWithEmbeddedBody([]);
        $two = $ro->body['two'];
        $this->assertInstanceOf(Request::class, $two);
        $ro->body = ['two' => $two, '_embedded' => clone $two];

        (string) $ro;

        $this->assertSame(['two', '_embedded'], array_keys(self::embedded($ro)));
    }

    public function testRenderingLeavesTheBodyTheResourceReturned(): void
    {
        $ro = $this->resourceWithEmbeddedBody([]);
        $returned = $ro->body;

        (string) $ro;

        $this->assertSame($returned, $ro->body);
    }

    /** @return iterable<string, array{mixed}> */
    public static function bodiesThatAreNotArrays(): iterable
    {
        yield 'a scalar' => ['1'];
        yield 'null' => [null];
        yield 'an object' => [new stdClass()];
    }

    #[DataProvider('bodiesThatAreNotArrays')]
    public function testRenderingLeavesABodyThatIsNotAnArray(mixed $body): void
    {
        $ro = $this->ro->onGet();
        $ro->body = $body;

        (string) $ro;

        $this->assertSame($body, $ro->body);
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
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testRenderNullBody(): void
    {
        $this->ro->body = null;
        $actual = (string) $this->ro;
        $expected = <<<'EOT'
{
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
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testBodyObject(): void
    {
        $this->ro->body = new class {
            public int $a = 1;
        };
        $actual = (string) $this->ro;

        $this->assertStringContainsString('"a": 1,', $actual);
    }

    /**
     * Regression: HalRenderer must evaluate embedded AbstractRequest instances
     * via __toString() so that lazy/batch decorators (e.g. bear/async's
     * AsyncRequest) get a chance to short-circuit invoke(). FakeLazyRequest
     * throws from its underlying invoker if invoke() is ever reached, so if
     * the renderer bypassed __toString() this test would fail.
     */
    public function testEmbedEvaluationGoesThroughToString(): void
    {
        $child = new FakeChild();
        $child->uri = new Uri('app://self/bear/resource/fakechild');
        $lazy = new FakeLazyRequest('{"name": "stubbed"}', $child);

        $this->ro->body = ['stub' => $lazy];

        $halRenderer = new HalRenderer(new HalLinker(new NullReverseLinker()));
        $halRenderer->renderHal($this->ro);

        $this->assertSame(['name' => 'stubbed'], self::embedded($this->ro)['stub']);
    }

    public function testDifferentSchemaEmbedSkipsRendererPrePass(): void
    {
        $child = new FakeChild();
        $child->uri = new Uri('page://self/bear/resource/fakechild');

        $this->ro->body = [
            'different' => new Request((new InvokerFactory())(), $child),
        ];

        $halRenderer = new HalRenderer(new HalLinker(new NullReverseLinker()));
        $halRenderer->renderHal($this->ro);

        $this->assertSame(['tree' => 3], self::embedded($this->ro)['different']);
        $this->assertStringNotContainsString('_links', (string) $child, 'the child of another scheme keeps its own renderer');
    }

    public function testDifferentSchemaEmbedDoesNotRunARequestThatAlreadyRan(): void
    {
        $invoker = new class ((new InvokerFactory())()) implements InvokerInterface {
            public int $runs = 0;

            public function __construct(private InvokerInterface $invoker)
            {
            }

            public function invoke(AbstractRequest $request): ResourceObject
            {
                $this->runs++;

                return $this->invoker->invoke($request);
            }
        };
        $child = new FakeChild();
        $child->uri = new Uri('page://self/bear/resource/fakechild');
        $request = new Request($invoker, $child);
        $this->ro->body = ['different' => $request];

        // Runs the request before rendering, as a cache dependency scan does
        (string) $request;
        (new HalRenderer(new HalLinker(new NullReverseLinker())))->renderHal($this->ro);

        $this->assertSame(1, $invoker->runs);
        $this->assertSame(['tree' => 3], self::embedded($this->ro)['different']);
    }

    private function resourceWithEmbeddedBody(mixed $embedded): ResourceObject
    {
        $ro = new class extends ResourceObject {
            public function onGet(): static
            {
                return $this;
            }
        };
        $child = (new FakeRo())(new FakeChild());
        $ro->uri = new Uri('app://self/embedded');
        $ro->body = [
            '_embedded' => $embedded,
            'two' => new Request((new InvokerFactory())(), $child),
        ];
        $ro->setRenderer(new HalRenderer(new HalLinker(new NullReverseLinker())));

        return $ro;
    }

    /** @return array<array-key, mixed> the _embedded of the rendered view */
    private static function embedded(ResourceObject $ro): array
    {
        /** @var array{_embedded: array<array-key, mixed>} $hal */
        $hal = json_decode((string) $ro->view, true, 512, JSON_THROW_ON_ERROR);

        return $hal['_embedded'];
    }

    /** @return array<string, array<string, string>> */
    private function renderLinkAttrLinks(): array
    {
        $ro = new FakeHalLinkAttr();
        $ro->uri = new Uri('app://self/dummy');
        $ro->setRenderer(new HalRenderer(new HalLinker(new NullReverseLinker())));
        /** @var array{_links: array<string, array<string, string>>} $hal */
        $hal = json_decode((string) $ro->onGet(), true, 512, JSON_THROW_ON_ERROR);

        return $hal['_links'];
    }

    public function testLinkAnnotationTitle(): void
    {
        $links = $this->renderLinkAttrLinks();

        $this->assertSame('Edit this', $links['edit']['title']);
    }

    public function testLinkAnnotationMethod(): void
    {
        $links = $this->renderLinkAttrLinks();

        $this->assertSame('put', $links['edit']['method']);
    }

    public function testLinkAnnotationDefaultMethodNotRendered(): void
    {
        $links = $this->renderLinkAttrLinks();

        $this->assertSame(['href' => '/profile'], $links['profile']);
    }
}
