<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ResourceLayoutException;
use BEAR\Resource\Interceptor\FakeResourceLayoutInterceptor;
use BEAR\Resource\Module\ResourceModule;
use FakeVendor\Sandbox\Resource\Page\Layout\Content;
use FakeVendor\Sandbox\Resource\Page\Layout\Footer;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

use function assert;
use function serialize;

class ResourceLayoutInterceptorTest extends TestCase
{
    private ResourceInterface $resource;

    protected function setUp(): void
    {
        $this->resource = (new Injector(new ResourceModule('FakeVendor\Sandbox'), __DIR__ . '/tmp'))->getInstance(ResourceInterface::class);
        Footer::$count = 0;
    }

    public function testPartsAreRequestsBeforeOnGet(): void
    {
        $ro = $this->resource->get('page://self/layout/content', ['id' => '1']);

        $this->assertSame('page://self/layout/footer?id=1&lang=ja', $ro->body['footerInOnGet']);
        $header = $ro->body['header'];
        assert($header instanceof Request);
        $this->assertSame('page://self/layout/header', $header->toUri());
    }

    public function testPositionalArgumentsExpandTheTemplate(): void
    {
        $ro = $this->resource->newInstance('page://self/layout/content');
        assert($ro instanceof Content);
        $ro->onGet('1', 'en');

        $this->assertSame('page://self/layout/footer?id=1&lang=en', $ro->body['footerInOnGet']);
    }

    public function testPartRunsOnceWhenRenderedAndSerialized(): void
    {
        $ro = $this->resource->get('page://self/layout/content', ['id' => '1']);
        $ro->setRenderer(new JsonRenderer());

        $this->assertStringContainsString('"footer":{"id":"1","lang":"ja"}', (string) $ro);
        serialize($ro);
        $this->assertSame(1, Footer::$count);
    }

    public function testMethodEmbedOverridesPartOfSameRel(): void
    {
        $ro = $this->resource->get('page://self/layout/both', ['id' => '1']);

        $uris = [];
        foreach (['header', 'footer', 'bird'] as $rel) {
            $request = $ro->body[$rel];
            assert($request instanceof Request);
            $uris[$rel] = $request->toUri();
        }

        $this->assertSame([
            'header' => 'page://self/layout/header',
            'footer' => 'app://self/bird/canary',
            'bird' => 'app://self/bird/sparrow?id=1',
        ], $uris);
    }

    public function testEmptyLayoutLeavesTheBodyAlone(): void
    {
        $ro = $this->resource->get('page://self/layout/bare');

        $this->assertNull($ro->body);
    }

    public function testMoreThanOneLayoutIsRejected(): void
    {
        $this->expectException(ResourceLayoutException::class);
        $this->expectExceptionMessage('FakeVendor\Sandbox\Resource\Page\Layout\TwoLayouts has more than one layout.');

        $this->resource->get('page://self/layout/two-layouts');
    }

    public function testInterceptorIsSwappable(): void
    {
        $module = new ResourceModule('FakeVendor\Sandbox');
        $module->override(new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(ResourceLayoutInterceptorInterface::class)->to(FakeResourceLayoutInterceptor::class);
            }
        });
        $resource = (new Injector($module, __DIR__ . '/tmp'))->getInstance(ResourceInterface::class);

        $ro = $resource->get('page://self/layout/content', ['id' => '1']);

        $this->assertSame(['swapped' => true, 'footerInOnGet' => null], $ro->body);
    }
}
