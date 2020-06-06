<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\RenderInterface;
use BEAR\Resource\NullOptionsRenderer;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class VoidOptionsMethodModuleTest extends TestCase
{
    public function testOptionsMethodModule() : void
    {
        $injector = new Injector(new VoidOptionsMethodModule);
        $renderer = $injector->getInstance(RenderInterface::class, 'options');
        $this->assertInstanceOf(NullOptionsRenderer::class, $renderer);
    }
}
