<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Module\JsonSchemaLinkHeaderModule;
use BEAR\Resource\Module\JsonSchemaModule;
use BEAR\Resource\Module\ResourceModule;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class PrefetchLinkHeaderInterceptorTest extends TestCase
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

    public function testDeclaredGetLinksAreAppendedUpToTheLimit(): void
    {
        $ro = $this->ro->onGet();

        $this->assertSame(
            '</user/20>; rel="prefetch", <https://example.com/p2>; rel="prefetch", </p3>; rel="prefetch", </p4>; rel="prefetch", </p5>; rel="prefetch", <http://example.com/schema/user.json>; rel="describedby"',
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
}
