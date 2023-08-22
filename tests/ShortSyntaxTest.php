<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Module\ResourceModule;
use FakeVendor\Sandbox\Resource\Page\Index;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function assert;

class ShortSyntaxTest extends TestCase
{
    private ResourceInterface $resource;

    protected function setUp(): void
    {
        $injector = new Injector(new FakeSchemeModule(new ResourceModule('FakeVendor\Sandbox')));
        $this->resource = $injector->getInstance(ResourceInterface::class);
    }

    /** @requires PHP 7.0.0 */
    public function testShortSyntax(): void
    {
        $ro = $this->resource->get('page://self/index', ['id' => 'koriym']);
        $this->assertInstanceOf(Index::class, $ro);
        $this->assertSame('koriym', $ro->body);
    }

    /** @requires PHP 7.0.0 */
    public function testShortSyntaxWithQuery(): void
    {
        $ro = $this->resource->get->uri('page://self/index?id=koriym')();
        /** @var ResourceObject $ro */
        $this->assertInstanceOf(Index::class, $ro);
        $this->assertSame('koriym', $ro->body);
    }

    public function testShortSyntaxInvoke(): void
    {
        $ro = $this->resource->get->uri('page://self/index?id=koriym')->__invoke(['id' => 'koriym']);
        $this->assertInstanceOf(Index::class, $ro);
        $this->assertSame('koriym', $ro->body);
    }

    public function testShortSyntaxFunction(): AbstractRequest
    {
        $index = $this->resource->get->uri('page://self/index?id=koriym');
        $ro = $index(['id' => 'koriym']);
        $this->assertInstanceOf(AbstractRequest::class, $index);
        $this->assertInstanceOf(Index::class, $ro);
        assert($index instanceof AbstractRequest);

        return $index;
    }

    /** @depends testShortSyntaxFunction */
    public function testShortSyntaxReuseRequest(AbstractRequest $index): void
    {
        $ro = $index(['id' => 'bear']);
        $this->assertSame('bear', $ro->body);
    }

    /** @requires PHP 7.0.0 */
    public function testShortSyntaxFunctionWithDefaultGetMethod(): void
    {
        $ro = $this->resource->uri('page://self/index')();
        $this->assertInstanceOf(Index::class, $ro);
    }
}
