<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Module\ResourceModule;
use FakeVendor\Sandbox\Resource\Page\Index;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class ShortSyntaxTest extends TestCase
{
    /**
     * @var ResourceInterface
     */
    private $resource;

    protected function setUp()
    {
        parent::setUp();
        $injector = new Injector(new FakeSchemeModule(new ResourceModule('FakeVendor\Sandbox'), $_ENV['TMP_DIR']));
        $this->resource = $injector->getInstance(ResourceInterface::class);
    }

    /**
     * @requires PHP 7.0.0
     */
    public function testShortSyntax()
    {
        $ro = $this->resource->get->uri('page://self/index')(['id' => 'koriym']);
        /* @var $ro ResourceObject */
        $this->assertInstanceOf(Index::class, $ro);
        $this->assertSame('koriym', $ro->body);
    }

    /**
     * @requires PHP 7.0.0
     */
    public function testShortSyntaxWithQuery()
    {
        $ro = $this->resource->get->uri('page://self/index?id=koriym')();
        /* @var $ro ResourceObject */
        $this->assertInstanceOf(Index::class, $ro);
        $this->assertSame('koriym', $ro->body);
    }

    public function testShortSyntaxInvoke()
    {
        $ro = $this->resource->get->uri('page://self/index?id=koriym')->__invoke(['id' => 'koriym']);
        $this->assertInstanceOf(Index::class, $ro);
        $this->assertSame('koriym', $ro->body);
    }

    public function testShortSyntaxFunction()
    {
        $index = $this->resource->get->uri('page://self/index?id=koriym');
        $ro = $index(['id' => 'koriym']);
        $this->assertInstanceOf(AbstractRequest::class, $index);
        $this->assertInstanceOf(Index::class, $ro);

        return $index;
    }

    /**
     * @depends testShortSyntaxFunction
     */
    public function testShortSyntaxReuseRequest(AbstractRequest $index)
    {
        $ro = $index(['id' => 'bear']);
        $this->assertSame('bear', $ro->body);
    }

    /**
     * @requires PHP 7.0.0
     */
    public function testShortSyntaxFunctionWithDefaultGetMethod()
    {
        $ro = $this->resource->uri('page://self/index')();
        $this->assertInstanceOf(Index::class, $ro);
    }
}
