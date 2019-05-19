<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\MethodNotAllowedException;
use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\Module\VoidOptionsMethodModule;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class VoidOptionsRendererTest extends TestCase
{
    /**
     * Test option renderer is disabled.
     *
     * VoidOptionsMethodModule supposed to install in production to disable "OPTIONS" method.
     */
    public function testVoidOptionsRenderer()
    {
        $this->expectException(MethodNotAllowedException::class);
        $injector = new Injector(new VoidOptionsMethodModule(new FakeSchemeModule(new ResourceModule('FakeVendor\Sandbox'))), __DIR__ . '/tmp');
        $resource = $injector->getInstance(ResourceInterface::class);
        /* @var $resource \BEAR\Resource\ResourceInterface */
        $resource->options->uri('page://self/index')->eager->request();
    }
}
