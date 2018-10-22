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
    public function testVoidOptionsRenderer()
    {
        $this->expectException(MethodNotAllowedException::class);
        $injector = new Injector(new VoidOptionsMethodModule(new FakeSchemeModule(new ResourceModule('FakeVendor\Sandbox'))), $_ENV['TMP_DIR']);
        $resource = $injector->getInstance(ResourceInterface::class);
        /* @var $resource \BEAR\Resource\ResourceInterface */
        $resource->options->uri('page://self/index')->eager->request();
    }
}
