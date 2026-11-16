<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Interceptor\FakeEmbedPageInterceptor;
use BEAR\Resource\Module\ResourceModule;
use FakeVendor\Sandbox\Resource\Page\EmbedPage\Footer;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

use function assert;
use function serialize;

class EmbedPageInterceptorTest extends TestCase
{
    private ResourceInterface $resource;

    protected function setUp(): void
    {
        $this->resource = (new Injector(new ResourceModule('FakeVendor\Sandbox'), __DIR__ . '/tmp'))->getInstance(ResourceInterface::class);
        Footer::$count = 0;
    }

    public function testPartsAreRequestsBeforeOnGet(): void
    {
        $ro = $this->resource->get('page://self/embed-page/content', ['id' => '1']);

        $this->assertSame('page://self/embed-page/footer?id=1&lang=ja', $ro->body['footerInOnGet']);
        $header = $ro->body['header'];
        assert($header instanceof Request);
        $this->assertSame('page://self/embed-page/header', $header->toUri());
    }

    public function testPartRunsOnceWhenRenderedAndSerialized(): void
    {
        $ro = $this->resource->get('page://self/embed-page/content', ['id' => '1']);
        $ro->setRenderer(new JsonRenderer());

        $this->assertStringContainsString('"footer":{"id":"1","lang":"ja"}', (string) $ro);
        serialize($ro);
        $this->assertSame(1, Footer::$count);
    }

    public function testMethodEmbedOverridesPartOfSameRel(): void
    {
        $ro = $this->resource->get('page://self/embed-page/both', ['id' => '1']);

        $uris = [];
        foreach (['header', 'footer', 'bird'] as $rel) {
            $request = $ro->body[$rel];
            assert($request instanceof Request);
            $uris[$rel] = $request->toUri();
        }

        $this->assertSame([
            'header' => 'page://self/embed-page/header',
            'footer' => 'app://self/bird/canary',
            'bird' => 'app://self/bird/sparrow?id=1',
        ], $uris);
    }

    public function testInterceptorIsSwappable(): void
    {
        $module = new ResourceModule('FakeVendor\Sandbox');
        $module->override(new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(EmbedPageInterceptorInterface::class)->to(FakeEmbedPageInterceptor::class);
            }
        });
        $resource = (new Injector($module, __DIR__ . '/tmp'))->getInstance(ResourceInterface::class);

        $ro = $resource->get('page://self/embed-page/content', ['id' => '1']);

        $this->assertSame(['swapped' => true, 'footerInOnGet' => null], $ro->body);
    }
}
