<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\HttpResourceObject;
use PHPUnit\Framework\TestCase;
use Ray\Compiler\CompiledInjector;
use Ray\Compiler\Compiler;

use function array_map;
use function glob;
use function unlink;

use const GLOB_BRACE;

final class HttpClientModuleTest extends TestCase
{
    protected function setUp(): void
    {
        @unlink(__DIR__ . '/tmp/compiled');
        array_map(unlink(...), (array) glob(__DIR__ . '/tmp/{*.php}', GLOB_BRACE)); // @phpstan-ignore-line
    }

    public function testHttpResourceObjectIsExplicitlyBound(): void
    {
        $scriptDir = __DIR__ . '/tmp';
        (new Compiler())->compile(new HttpClientModule(), $scriptDir);
        $injector = new CompiledInjector($scriptDir);

        $this->assertInstanceOf(HttpResourceObject::class, $injector->getInstance(HttpResourceObject::class));
    }
}
