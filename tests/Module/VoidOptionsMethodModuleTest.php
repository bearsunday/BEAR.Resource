<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\RenderInterface;
use BEAR\Resource\VoidOptionsRenderer;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class VoidOptionsMethodModuleTest extends TestCase
{
    public function testOptionsMethodModule()
    {
        $injector = new Injector(new VoidOptionsMethodModule);
        $renderer = $injector->getInstance(RenderInterface::class, 'options');
        $this->assertInstanceOf(VoidOptionsRenderer::class, $renderer);
    }
}
