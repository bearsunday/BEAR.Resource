<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\RenderInterface;
use BEAR\Resource\VoidOptionsRenderer;
use Ray\Di\AbstractModule;

class VoidOptionsMethodModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind(RenderInterface::class)->annotatedWith('options')->to(VoidOptionsRenderer::class);
    }
}
