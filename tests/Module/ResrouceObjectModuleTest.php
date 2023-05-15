<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\FakeLazyModule;
use BEAR\Resource\ResourceObject;
use FakeVendor\Sandbox\Resource\Page\HelloWorld;
use FakeVendor\Sandbox\Resource\Page\Index;
use Generator;
use PHPUnit\Framework\TestCase;
use Ray\Compiler\CompileInjector;

use function array_map;
use function glob;
use function iterator_to_array;
use function unlink;

use const GLOB_BRACE;

final class ResrouceObjectModuleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        @unlink(__DIR__ . '/tmp/compiled');
        array_map('unlink', (array) glob(__DIR__ . '/tmp/{*.php}', GLOB_BRACE)); // @phpstan-ignore-line
    }

    public function testConfigureWithGenerator(): void
    {
        $injector = new CompileInjector(
            __DIR__ . '/tmp',
            new FakeLazyModule(new ResourceObjectModule($this->getResourceObjectGenerator())),
        );

        $this->assertInstanceOf(Index::class, $injector->getInstance(Index::class));
        $this->assertInstanceOf(HelloWorld::class, $injector->getInstance(HelloWorld::class));
    }

    public function testConfigureWithArray(): void
    {
        $injector = new CompileInjector(
            __DIR__ . '/tmp',
            new FakeLazyModule(new ResourceObjectModule(iterator_to_array($this->getResourceObjectGenerator()))),
        );

        $this->assertInstanceOf(Index::class, $injector->getInstance(Index::class));
        $this->assertInstanceOf(HelloWorld::class, $injector->getInstance(HelloWorld::class));
    }

    /** @return Generator<class-string<ResourceObject>> */
    private function getResourceObjectGenerator(): Generator
    {
        yield Index::class;
        yield HelloWorld::class;
    }
}
