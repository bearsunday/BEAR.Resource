<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\HalRenderer;
use BEAR\Resource\RenderInterface;
use Ray\Di\AbstractModule;

class HalModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->bind(RenderInterface::class)->to(HalRenderer::class);
    }
}
