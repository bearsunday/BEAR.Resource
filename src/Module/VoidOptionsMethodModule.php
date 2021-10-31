<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\NullOptionsRenderer;
use BEAR\Resource\RenderInterface;
use Ray\Di\AbstractModule;

final class VoidOptionsMethodModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->bind(RenderInterface::class)->annotatedWith('options')->to(NullOptionsRenderer::class);
    }
}
