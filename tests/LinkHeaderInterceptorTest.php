<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Module\JsonSchemaLinkHeaderModule;
use BEAR\Resource\Module\JsonSchemaModule;
use BEAR\Resource\Module\ResourceModule;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;
use ReflectionClass;

class LinkHeaderInterceptorTest extends TestCase
{
    private Injector $injector;
    private FakePrefetch $ro;

    protected function setUp(): void
    {
        $fakeDir = __DIR__ . '/Fake';
        $module = new JsonSchemaModule($fakeDir . '/json_schema', $fakeDir . '/json_validate', new ResourceModule('FakeVendor\Sandbox'));
        $module = new JsonSchemaLinkHeaderModule('http://example.com/schema/', $module);
        $this->injector = new Injector($module, __DIR__ . '/tmp');
        $this->ro = $this->injector->getInstance(FakePrefetch::class);
    }

    public function testOnlyPrefetchLinksAreLimited(): void
    {
        $ro = $this->ro->onGet();

        $this->assertSame(
            '</user/20>; rel="prefetch", </next>; rel="next", <https://example.com/p2>; rel="prefetch", </p3>; rel="prefetch", </p4>; rel="prefetch", </p5>; rel="prefetch", </last>; rel="last", <http://example.com/schema/user.json>; rel="describedby"',
            $ro->headers['Link'],
        );
    }

    public function testPagingAndCanonicalLinksAreAppended(): void
    {
        $ro = $this->injector->getInstance(FakePaging::class)->onGet();

        $this->assertSame(
            '<https://example.com/articles>; rel="canonical", </articles?page=1>; rel="first", </articles?page=2>; rel="next", </articles?page=9>; rel="last"',
            $ro->headers['Link'],
        );
    }

    public function testNoHeaderUnlessCode200(): void
    {
        $ro = $this->ro->onGet(404);

        $this->assertArrayNotHasKey('Link', $ro->headers);
    }

    public function testAReturnedBodyExpandsTheLink(): void
    {
        $ro = $this->injector->getInstance(FakePrefetchBody::class);

        $this->assertSame(['next' => 2, 'host' => '//evil.example/x', 'empty' => ''], $ro->onGet());
        $this->assertSame('</article/2>; rel="prefetch"', $ro->headers['Link'], 'a protocol-relative href and an empty variable are skipped');
    }

    public function testAMethodWithoutALinkHeaderRelIsNotIntercepted(): void
    {
        $ro = $this->injector->getInstance(FakeHalLinkAttr::class);

        $this->assertSame(FakeHalLinkAttr::class, $ro::class);
    }

    public function testTheMatcherMatchesNoClass(): void
    {
        $this->assertFalse((new LinkHeaderMatcher())->matchesClass(new ReflectionClass(FakePrefetch::class), []));
    }
}
